export const translations = {
  en: {
    meta: {
      title: 'Signalist — AI-powered intelligence for your team',
      description:
        'Monitor industry news, curate the best content, and publish AI-powered briefings in minutes. Built for comms teams, marketing agencies, and media monitors.',
    },
    nav: {
      features: 'Features',
      pricing: 'Pricing',
      cta: 'Join waitlist',
    },
    hero: {
      badge: 'Now in early access — join the waitlist',
      headline: 'Stop drowning in news.',
      headlineAccent: 'Start leading the conversation.',
      subheadline:
        'Signalist monitors hundreds of RSS feeds, surfaces what matters to your industry, and helps your team publish AI-powered briefings in minutes — not hours.',
      primaryCta: 'Get early access',
      secondaryCta: 'See how it works',
      articles: [
        { tag: 'AI', title: 'OpenAI announces GPT-5 with improved reasoning capabilities', source: 'TechCrunch', time: '2m ago', read: false },
        { tag: 'Marketing', title: 'How leading brands are leveraging AI for content strategy in 2026', source: 'Marketing Week', time: '14m ago', read: false },
        { tag: 'AI', title: 'Anthropic raises $3B Series D to accelerate safety research', source: 'The Verge', time: '1h ago', read: true },
        { tag: 'Industry', title: 'European media companies report 40% increase in digital subscriptions', source: 'Reuters', time: '2h ago', read: true },
      ],
      urlPreview: 'signalist.app/inbox',
    },
    features: {
      label: 'How it works',
      headline: 'From raw feeds to polished insights',
      subheadline: 'A four-step loop your comms team runs on autopilot.',
      items: [
        {
          step: '01',
          title: 'Monitor',
          headline: 'One inbox for every source that matters',
          description:
            'Aggregate hundreds of RSS feeds across categories. Filter by keyword, topic, or relevance score. Get a clean, unified inbox instead of a dozen browser tabs.',
        },
        {
          step: '02',
          title: 'Curate',
          headline: "AI reads it so your team doesn't have to",
          description:
            'Automatic summaries, AI-generated tags, and relevance scoring cut reading time by 80%. Bookmark what matters with a single click and annotate for context.',
        },
        {
          step: '03',
          title: 'Synthesize',
          headline: 'Weekly briefings that write themselves',
          description:
            'Generate AI-powered newsletters from your curated bookmarks. Configure reading time (5 min, 10 min), schedule delivery, and let Signalist handle the rest.',
        },
        {
          step: '04',
          title: 'Publish',
          headline: 'Share insights across every channel',
          description:
            'Post AI-generated recaps to LinkedIn, X, and more in one click. Schedule your weekly social recap and maintain a consistent presence without the manual effort.',
        },
      ],
    },
    pricing: {
      label: 'Pricing',
      headline: 'Simple, transparent pricing',
      subheadline: 'No hidden fees. Cancel anytime.',
      popularBadge: 'Most popular',
      featureLabels: ['RSS feeds', 'Users', 'Articles', 'Search', 'Bookmarks', 'AI features', 'Publishing'],
      plans: [
        {
          name: 'Free',
          price: '€0',
          period: 'forever',
          description: 'For individuals and small teams getting started with content monitoring.',
          cta: 'Get started free',
          ctaHref: '#waitlist',
          featured: false,
          limits: ['5 RSS feeds', '1 user', '100 articles / day', 'Full-text search', 'Bookmarks', '—', '—'],
        },
        {
          name: 'Team',
          price: '€29',
          period: 'per month',
          description: 'For comms teams and marketing agencies who need AI and collaboration.',
          cta: 'Join waitlist',
          ctaHref: '#waitlist',
          featured: true,
          limits: ['30 RSS feeds', 'Up to 5 users', 'Unlimited articles', 'Full-text + semantic search', 'Bookmarks + annotations', 'AI summaries & auto-tagging', 'AI newsletters & social recap'],
        },
        {
          name: 'Enterprise',
          price: 'Custom',
          period: 'contact us',
          description: 'For organisations with advanced needs, SSO, and dedicated support.',
          cta: 'Contact us',
          ctaHref: 'mailto:hello@signalist.app',
          featured: false,
          limits: ['Unlimited feeds', 'Unlimited users', 'Unlimited articles', 'Full-text + semantic search', 'Bookmarks + annotations', 'AI summaries & auto-tagging', 'AI newsletters, recap & API'],
        },
      ],
    },
    socialProof: {
      label: 'Early feedback',
      headline: 'Trusted by teams who live in their inbox',
      testimonials: [
        {
          quote: "We used to spend 2 hours every Monday morning scanning industry news. Signalist cut that to 15 minutes and our briefings are actually better now.",
          author: 'Sophie M.',
          role: 'Head of Communications',
          company: 'European Media Group',
          initials: 'SM',
        },
        {
          quote: "The AI summaries are surprisingly good. We pipe our curated feed straight into our weekly LinkedIn post — it's become our best-performing content.",
          author: 'Lucas D.',
          role: 'Content Strategist',
          company: 'Digital Marketing Agency',
          initials: 'LD',
        },
        {
          quote: "Finally a tool built for teams, not just personal reading. The shared workspace and role system made it easy to onboard our whole comms department.",
          author: 'Amira K.',
          role: 'Marketing Director',
          company: 'B2B SaaS Company',
          initials: 'AK',
        },
      ],
    },
    waitlist: {
      headline: 'Be first in line.',
      subheadline:
        'Signalist is in early access. Join the waitlist and get notified when your spot is ready — plus a 3-month Team plan discount for early members.',
      placeholder: 'you@company.com',
      button: 'Join waitlist',
      consent: 'I agree to receive product updates from Signalist. No spam, unsubscribe at any time. See our',
      privacyLink: 'privacy policy',
    },
    footer: {
      features: 'Features',
      pricing: 'Pricing',
      privacy: 'Privacy',
      terms: 'Terms',
      contact: 'Contact',
      copyright: 'All rights reserved.',
    },
  },

  fr: {
    meta: {
      title: 'Signalist — Intelligence IA pour votre équipe',
      description:
        "Surveillez l'actualité de votre secteur, curez les meilleurs contenus et publiez des briefings IA en quelques minutes. Conçu pour les équipes comms, agences marketing et cellules de veille.",
    },
    nav: {
      features: 'Fonctionnalités',
      pricing: 'Tarifs',
      cta: 'Rejoindre la liste',
    },
    hero: {
      badge: "Accès anticipé disponible — rejoignez la liste d'attente",
      headline: "Fini de se noyer dans l'actualité.",
      headlineAccent: 'Prenez le lead.',
      subheadline:
        "Signalist surveille des centaines de flux RSS, fait remonter ce qui compte pour votre secteur et aide votre équipe à publier des briefings alimentés par l'IA en quelques minutes — pas en heures.",
      primaryCta: 'Obtenir un accès anticipé',
      secondaryCta: 'Voir comment ça marche',
      articles: [
        { tag: 'IA', title: "OpenAI annonce GPT-5 avec des capacités de raisonnement améliorées", source: 'TechCrunch', time: 'il y a 2 min', read: false },
        { tag: 'Marketing', title: "Comment les grandes marques exploitent l'IA pour leur stratégie de contenu", source: 'Marketing Week', time: 'il y a 14 min', read: false },
        { tag: 'IA', title: "Anthropic lève 3 Md$ en Série D pour accélérer sa recherche en sécurité", source: 'The Verge', time: 'il y a 1h', read: true },
        { tag: 'Secteur', title: "Les médias européens affichent +40 % d'abonnements numériques", source: 'Reuters', time: 'il y a 2h', read: true },
      ],
      urlPreview: 'signalist.app/inbox',
    },
    features: {
      label: 'Comment ça marche',
      headline: 'Des flux bruts aux insights synthétisés',
      subheadline: 'Une boucle en quatre étapes que votre équipe comms fait tourner en automatique.',
      items: [
        {
          step: '01',
          title: 'Surveiller',
          headline: 'Une boîte de réception unique pour toutes vos sources',
          description:
            "Agrégez des centaines de flux RSS par catégories. Filtrez par mot-clé, sujet ou score de pertinence. Retrouvez une boîte de réception claire à la place d'une dizaine d'onglets.",
        },
        {
          step: '02',
          title: 'Sélectionner',
          headline: "L'IA lit à la place de votre équipe",
          description:
            "Résumés automatiques, tags générés par l'IA et score de pertinence réduisent le temps de lecture de 80 %. Bookmarkez ce qui compte en un clic et annotez pour contextualiser.",
        },
        {
          step: '03',
          title: 'Synthétiser',
          headline: 'Des briefings hebdomadaires qui se rédigent tout seuls',
          description:
            "Générez des newsletters IA à partir de vos bookmarks. Configurez le temps de lecture (5 min, 10 min), programmez la livraison et laissez Signalist faire le reste.",
        },
        {
          step: '04',
          title: 'Publier',
          headline: 'Diffusez vos insights sur tous les canaux',
          description:
            "Postez des récapitulatifs générés par l'IA sur LinkedIn, X et plus encore en un clic. Programmez votre récap social hebdomadaire et maintenez une présence régulière sans l'effort manuel.",
        },
      ],
    },
    pricing: {
      label: 'Tarifs',
      headline: 'Des tarifs simples et transparents',
      subheadline: 'Sans frais cachés. Résiliable à tout moment.',
      popularBadge: 'Le plus populaire',
      featureLabels: ['Flux RSS', 'Utilisateurs', 'Articles', 'Recherche', 'Favoris', 'Fonctions IA', 'Publication'],
      plans: [
        {
          name: 'Free',
          price: '€0',
          period: 'pour toujours',
          description: 'Pour les indépendants et les petites équipes qui démarrent avec la veille de contenu.',
          cta: 'Commencer gratuitement',
          ctaHref: '#waitlist',
          featured: false,
          limits: ['5 flux RSS', '1 utilisateur', '100 articles / jour', 'Recherche plein texte', 'Favoris', '—', '—'],
        },
        {
          name: 'Team',
          price: '€29',
          period: 'par mois',
          description: "Pour les équipes comms et agences marketing qui ont besoin de l'IA et de la collaboration.",
          cta: 'Rejoindre la liste',
          ctaHref: '#waitlist',
          featured: true,
          limits: ["30 flux RSS", "Jusqu'à 5 utilisateurs", 'Articles illimités', 'Recherche plein texte + sémantique', 'Favoris + annotations', "Résumés IA & auto-tagging", 'Newsletters IA & récap social'],
        },
        {
          name: 'Enterprise',
          price: 'Sur mesure',
          period: 'contactez-nous',
          description: 'Pour les organisations avec des besoins avancés, SSO et support dédié.',
          cta: 'Nous contacter',
          ctaHref: 'mailto:hello@signalist.app',
          featured: false,
          limits: ['Flux illimités', 'Utilisateurs illimités', 'Articles illimités', 'Recherche plein texte + sémantique', 'Favoris + annotations', "Résumés IA & auto-tagging", 'Newsletters IA, récap & API'],
        },
      ],
    },
    socialProof: {
      label: 'Premiers retours',
      headline: "Plébiscité par les équipes qui vivent dans leur boîte de réception",
      testimonials: [
        {
          quote: "Avant, on passait 2 heures chaque lundi matin à parcourir l'actualité. Signalist a réduit ça à 15 minutes, et nos briefings sont objectivement meilleurs.",
          author: 'Sophie M.',
          role: 'Directrice de la Communication',
          company: 'Groupe Médias Européen',
          initials: 'SM',
        },
        {
          quote: "Les résumés IA sont bluffants. On alimente directement notre récap LinkedIn depuis notre flux curé — c'est devenu notre contenu le plus performant.",
          author: 'Lucas D.',
          role: 'Stratégiste Contenu',
          company: 'Agence Marketing Digital',
          initials: 'LD',
        },
        {
          quote: "Enfin un outil pensé pour les équipes, pas juste pour la lecture perso. L'espace partagé et le système de rôles ont facilité l'onboarding de toute notre direction comms.",
          author: 'Amira K.',
          role: 'Directrice Marketing',
          company: 'Éditeur SaaS B2B',
          initials: 'AK',
        },
      ],
    },
    waitlist: {
      headline: 'Réservez votre place.',
      subheadline:
        "Signalist est en accès anticipé. Rejoignez la liste d'attente et soyez notifié quand votre accès est prêt — plus une remise de 3 mois sur le plan Team pour les premiers membres.",
      placeholder: 'vous@entreprise.com',
      button: "Rejoindre la liste",
      consent: "J'accepte de recevoir les actualités produit de Signalist. Pas de spam, désinscription à tout moment. Voir notre",
      privacyLink: 'politique de confidentialité',
    },
    footer: {
      features: 'Fonctionnalités',
      pricing: 'Tarifs',
      privacy: 'Confidentialité',
      terms: 'CGU',
      contact: 'Contact',
      copyright: 'Tous droits réservés.',
    },
  },
} as const;

export type Locale = keyof typeof translations;
export type Translations = typeof translations['en'] | typeof translations['fr'];
