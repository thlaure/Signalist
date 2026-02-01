-- Enable pgvector extension
CREATE EXTENSION IF NOT EXISTS vector;

-- Verify extension is installed
SELECT * FROM pg_extension WHERE extname = 'vector';

-- Grant privileges
GRANT ALL PRIVILEGES ON DATABASE signalist TO signalist;

-- Create test database
CREATE DATABASE signalist_test OWNER signalist;

-- Connect to test database and enable pgvector
\c signalist_test
CREATE EXTENSION IF NOT EXISTS vector;
GRANT ALL PRIVILEGES ON DATABASE signalist_test TO signalist;
