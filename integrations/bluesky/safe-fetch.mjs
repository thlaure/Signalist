import { lookup } from 'node:dns/promises';
import { BlockList, isIP } from 'node:net';
import { Agent } from 'undici';

const blocked4 = new BlockList();
const blocked6 = new BlockList();
for (const [address, prefix] of [['0.0.0.0', 8], ['10.0.0.0', 8], ['100.64.0.0', 10], ['127.0.0.0', 8], ['169.254.0.0', 16], ['172.16.0.0', 12], ['192.0.0.0', 24], ['192.0.2.0', 24], ['192.168.0.0', 16], ['198.18.0.0', 15], ['198.51.100.0', 24], ['203.0.113.0', 24], ['224.0.0.0', 4], ['240.0.0.0', 4]]) blocked4.addSubnet(address, prefix, 'ipv4');
for (const [address, prefix] of [['::', 128], ['::1', 128], ['::ffff:0:0', 96], ['fc00::', 7], ['fe80::', 10], ['fec0::', 10], ['ff00::', 8], ['2001:db8::', 32], ['2001::', 32], ['2002::', 16]]) blocked6.addSubnet(address, prefix, 'ipv6');

export function publicAddress(address) {
  const family = isIP(address);
  if (!family) return false;
  if (family === 6 && !/^[23][0-9a-f]{3}:/i.test(address)) return false;
  return family === 4 ? !blocked4.check(address, 'ipv4') : !blocked6.check(address, 'ipv6');
}

export function safeTarget(input) {
  const url = new URL(typeof input === 'string' || input instanceof URL ? input : input.url);
  if (url.protocol !== 'https:' || url.username || url.password || (url.port && url.port !== '443')) throw new Error('Unsafe provider URL');
  const hostname = url.hostname.replace(/^\[|\]$/g, '');
  if (hostname === 'localhost' || hostname.endsWith('.localhost') || (isIP(hostname) && !publicAddress(hostname))) throw new Error('Unsafe provider URL');
  return url;
}

const dispatcher = new Agent({ connect: { lookup(hostname, options, callback) {
  lookup(hostname, { all: true, verbatim: true }).then((records) => {
    const safe = records.filter((record) => publicAddress(record.address) && (!options.family || options.family === record.family));
    if (!safe.length) return callback(new Error('Provider address is not public'));
    if (options.all) callback(null, safe);
    else callback(null, safe[0].address, safe[0].family);
  }).catch(() => callback(new Error('Provider address unavailable')));
} } });

export async function safeFetch(input, init = {}) {
  safeTarget(input);
  const signal = init.signal ? AbortSignal.any([init.signal, AbortSignal.timeout(15_000)]) : AbortSignal.timeout(15_000);
  const response = await fetch(input, { ...init, signal, redirect: 'manual', dispatcher });
  if (response.status >= 300 && response.status < 400) { await response.body?.cancel(); throw new Error('Provider redirects are not permitted'); }
  if (!response.body) return response;
  const reader = response.body.getReader(); let bytes = 0;
  const limited = new Response(new ReadableStream({
    async pull(controller) {
      try {
        const next = await reader.read();
        if (next.done) { controller.close(); return; }
        bytes += next.value.byteLength;
        if (bytes > 1048576) { await reader.cancel(); controller.error(new Error('Provider response too large')); return; }
        controller.enqueue(next.value);
      } catch { controller.error(new Error('Provider response unavailable')); }
    },
    cancel(reason) { return reader.cancel(reason); },
  }), { status: response.status, statusText: response.statusText, headers: response.headers });
  Object.defineProperty(limited, 'url', { value: response.url });
  return limited;
}

export async function closeFetch() { await dispatcher.close(); }
