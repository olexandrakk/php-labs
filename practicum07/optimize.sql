USE practicum4;

EXPLAIN SELECT * FROM topics WHERE author = 'Seed Author 5';

CREATE INDEX idx_topics_author ON topics (author);

EXPLAIN SELECT * FROM topics WHERE author = 'Seed Author 5';