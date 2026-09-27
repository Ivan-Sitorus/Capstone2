SELECT 'CREATE DATABASE minepos_testing'
WHERE NOT EXISTS (SELECT FROM pg_database WHERE datname = 'minepos_testing')\gexec
