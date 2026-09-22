SELECT 'CREATE DATABASE pos_cafe_testing'
WHERE NOT EXISTS (SELECT FROM pg_database WHERE datname = 'pos_cafe_testing')\gexec
