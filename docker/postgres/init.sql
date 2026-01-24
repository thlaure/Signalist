-- Enable pgvector extension
CREATE EXTENSION IF NOT EXISTS vector;

-- Verify extension is installed
SELECT * FROM pg_extension WHERE extname = 'vector';

-- Grant privileges
GRANT ALL PRIVILEGES ON DATABASE signalist TO signalist;
