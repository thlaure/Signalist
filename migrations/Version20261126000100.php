<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261126000100 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Remove DBAL 3 type comments (DC2Type), which DBAL 4 no longer uses.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('COMMENT ON COLUMN subscription.id IS NULL');
        $this->addSql('COMMENT ON COLUMN subscription.owner_id IS NULL');
        $this->addSql('COMMENT ON COLUMN subscription.organization_id IS NULL');
        $this->addSql('COMMENT ON COLUMN category.id IS NULL');
        $this->addSql('COMMENT ON COLUMN category.created_at IS NULL');
        $this->addSql('COMMENT ON COLUMN category.updated_at IS NULL');
        $this->addSql('COMMENT ON COLUMN category.owner_id IS NULL');
        $this->addSql('COMMENT ON COLUMN category.organization_id IS NULL');
        $this->addSql('COMMENT ON COLUMN feed.id IS NULL');
        $this->addSql('COMMENT ON COLUMN feed.category_id IS NULL');
        $this->addSql('COMMENT ON COLUMN feed.last_fetched_at IS NULL');
        $this->addSql('COMMENT ON COLUMN feed.created_at IS NULL');
        $this->addSql('COMMENT ON COLUMN feed.updated_at IS NULL');
        $this->addSql('COMMENT ON COLUMN feed.owner_id IS NULL');
        $this->addSql('COMMENT ON COLUMN feed.organization_id IS NULL');
        $this->addSql('COMMENT ON COLUMN user_consent.id IS NULL');
        $this->addSql('COMMENT ON COLUMN user_consent.user_id IS NULL');
        $this->addSql('COMMENT ON COLUMN user_consent.created_at IS NULL');
        $this->addSql('COMMENT ON COLUMN bookmark_sync_run.id IS NULL');
        $this->addSql('COMMENT ON COLUMN bookmark_sync_run.account_id IS NULL');
        $this->addSql('COMMENT ON COLUMN bookmark_sync_run.created_at IS NULL');
        $this->addSql('COMMENT ON COLUMN newsletter_run.id IS NULL');
        $this->addSql('COMMENT ON COLUMN newsletter_run.newsletter_id IS NULL');
        $this->addSql('COMMENT ON COLUMN newsletter_run.created_at IS NULL');
        $this->addSql('COMMENT ON COLUMN data_access_audit.id IS NULL');
        $this->addSql('COMMENT ON COLUMN data_access_audit.user_id IS NULL');
        $this->addSql('COMMENT ON COLUMN data_access_audit.created_at IS NULL');
        $this->addSql('COMMENT ON COLUMN seat_sync_request.id IS NULL');
        $this->addSql('COMMENT ON COLUMN seat_sync_request.subscription_id IS NULL');
        $this->addSql('COMMENT ON COLUMN newsletter.id IS NULL');
        $this->addSql('COMMENT ON COLUMN newsletter.owner_id IS NULL');
        $this->addSql('COMMENT ON COLUMN newsletter.next_run_at IS NULL');
        $this->addSql('COMMENT ON COLUMN newsletter.generated_at IS NULL');
        $this->addSql('COMMENT ON COLUMN newsletter.created_at IS NULL');
        $this->addSql('COMMENT ON COLUMN oauth_sdk_store.id IS NULL');
        $this->addSql('COMMENT ON COLUMN oauth_sdk_store.owner_id IS NULL');
        $this->addSql('COMMENT ON COLUMN oauth_sdk_store.expires_at IS NULL');
        $this->addSql('COMMENT ON COLUMN bookmark_annotation.id IS NULL');
        $this->addSql('COMMENT ON COLUMN bookmark_annotation.bookmark_id IS NULL');
        $this->addSql('COMMENT ON COLUMN bookmark_annotation.author_id IS NULL');
        $this->addSql('COMMENT ON COLUMN bookmark_annotation.created_at IS NULL');
        $this->addSql('COMMENT ON COLUMN article_read_state.id IS NULL');
        $this->addSql('COMMENT ON COLUMN article_read_state.article_id IS NULL');
        $this->addSql('COMMENT ON COLUMN article_read_state.user_id IS NULL');
        $this->addSql('COMMENT ON COLUMN article_search.id IS NULL');
        $this->addSql('COMMENT ON COLUMN article_search.owner_id IS NULL');
        $this->addSql('COMMENT ON COLUMN article_search.created_at IS NULL');
        $this->addSql('COMMENT ON COLUMN raindrop_link.id IS NULL');
        $this->addSql('COMMENT ON COLUMN raindrop_link.account_id IS NULL');
        $this->addSql('COMMENT ON COLUMN raindrop_link.article_id IS NULL');
        $this->addSql('COMMENT ON COLUMN article_embedding.id IS NULL');
        $this->addSql('COMMENT ON COLUMN article_embedding.article_id IS NULL');
        $this->addSql('COMMENT ON COLUMN article_embedding.embedding IS NULL');
        $this->addSql('COMMENT ON COLUMN article_embedding.created_at IS NULL');
        $this->addSql('COMMENT ON COLUMN crosspost.id IS NULL');
        $this->addSql('COMMENT ON COLUMN crosspost.account_id IS NULL');
        $this->addSql('COMMENT ON COLUMN crosspost.approved_at IS NULL');
        $this->addSql('COMMENT ON COLUMN tag.id IS NULL');
        $this->addSql('COMMENT ON COLUMN tag.owner_id IS NULL');
        $this->addSql('COMMENT ON COLUMN shared_bookmark.id IS NULL');
        $this->addSql('COMMENT ON COLUMN shared_bookmark.organization_id IS NULL');
        $this->addSql('COMMENT ON COLUMN shared_bookmark.article_id IS NULL');
        $this->addSql('COMMENT ON COLUMN shared_bookmark.curator_id IS NULL');
        $this->addSql('COMMENT ON COLUMN shared_bookmark.created_at IS NULL');
        $this->addSql('COMMENT ON COLUMN "user".id IS NULL');
        $this->addSql('COMMENT ON COLUMN "user".created_at IS NULL');
        $this->addSql('COMMENT ON COLUMN "user".updated_at IS NULL');
        $this->addSql('COMMENT ON COLUMN "user".deleted_at IS NULL');
        $this->addSql('COMMENT ON COLUMN "user".email_verified_at IS NULL');
        $this->addSql('COMMENT ON COLUMN "user".paid_until IS NULL');
        $this->addSql('COMMENT ON COLUMN billing_event.created_at IS NULL');
        $this->addSql('COMMENT ON COLUMN external_account.id IS NULL');
        $this->addSql('COMMENT ON COLUMN external_account.owner_id IS NULL');
        $this->addSql('COMMENT ON COLUMN external_account.expires_at IS NULL');
        $this->addSql('COMMENT ON COLUMN external_account.last_synced_at IS NULL');
        $this->addSql('COMMENT ON COLUMN usage_counter.id IS NULL');
        $this->addSql('COMMENT ON COLUMN organization_activity.id IS NULL');
        $this->addSql('COMMENT ON COLUMN organization_activity.organization_id IS NULL');
        $this->addSql('COMMENT ON COLUMN organization_activity.actor_id IS NULL');
        $this->addSql('COMMENT ON COLUMN organization_activity.created_at IS NULL');
        $this->addSql('COMMENT ON COLUMN social_recap.id IS NULL');
        $this->addSql('COMMENT ON COLUMN social_recap.owner_id IS NULL');
        $this->addSql('COMMENT ON COLUMN social_recap.created_at IS NULL');
        $this->addSql('COMMENT ON COLUMN social_recap.period_start IS NULL');
        $this->addSql('COMMENT ON COLUMN social_recap.period_end IS NULL');
        $this->addSql('COMMENT ON COLUMN bookmark.id IS NULL');
        $this->addSql('COMMENT ON COLUMN bookmark.article_id IS NULL');
        $this->addSql('COMMENT ON COLUMN bookmark.created_at IS NULL');
        $this->addSql('COMMENT ON COLUMN billing_link.id IS NULL');
        $this->addSql('COMMENT ON COLUMN billing_link.subscription_id IS NULL');
        $this->addSql('COMMENT ON COLUMN billing_link.requested_by_id IS NULL');
        $this->addSql('COMMENT ON COLUMN billing_link.created_at IS NULL');
        $this->addSql('COMMENT ON COLUMN oauth_attempt.id IS NULL');
        $this->addSql('COMMENT ON COLUMN oauth_attempt.owner_id IS NULL');
        $this->addSql('COMMENT ON COLUMN oauth_attempt.expires_at IS NULL');
        $this->addSql('COMMENT ON COLUMN waitlist_entry.id IS NULL');
        $this->addSql('COMMENT ON COLUMN waitlist_entry.created_at IS NULL');
        $this->addSql('COMMENT ON COLUMN waitlist_entry.confirmed_at IS NULL');
        $this->addSql('COMMENT ON COLUMN organization_member.id IS NULL');
        $this->addSql('COMMENT ON COLUMN organization_member.organization_id IS NULL');
        $this->addSql('COMMENT ON COLUMN organization_member.user_id IS NULL');
        $this->addSql('COMMENT ON COLUMN weekly_digest.id IS NULL');
        $this->addSql('COMMENT ON COLUMN weekly_digest.owner_id IS NULL');
        $this->addSql('COMMENT ON COLUMN weekly_digest.sent_at IS NULL');
        $this->addSql('COMMENT ON COLUMN weekly_digest.updated_at IS NULL');
        $this->addSql('COMMENT ON COLUMN weekly_digest.dispatched_at IS NULL');
        $this->addSql('COMMENT ON COLUMN organization_invitation.id IS NULL');
        $this->addSql('COMMENT ON COLUMN organization_invitation.organization_id IS NULL');
        $this->addSql('COMMENT ON COLUMN organization_invitation.expires_at IS NULL');
        $this->addSql('COMMENT ON COLUMN organization_invitation.accepted_at IS NULL');
        $this->addSql('COMMENT ON COLUMN article.id IS NULL');
        $this->addSql('COMMENT ON COLUMN article.feed_id IS NULL');
        $this->addSql('COMMENT ON COLUMN article.published_at IS NULL');
        $this->addSql('COMMENT ON COLUMN article.created_at IS NULL');
        $this->addSql('COMMENT ON COLUMN article.enriched_at IS NULL');
        $this->addSql('COMMENT ON COLUMN organization.id IS NULL');
        $this->addSql('COMMENT ON COLUMN organization.created_at IS NULL');
        $this->addSql('COMMENT ON COLUMN organization.paid_until IS NULL');
    }

    public function down(Schema $schema): void
    {
        // Intentionally empty: DBAL 4 ignores type comments, so restoring them has no effect.
    }
}
