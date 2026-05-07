## 2026-05-07: Generated datamining/requirements.txt

### External packages identified from imports
| Package | Pip Name | Used In |
|---------|----------|---------|
| fastapi | fastapi | api.py |
| uvicorn | uvicorn | (server runner) |
| psycopg2 | psycopg2-binary | api.py |
| scikit-learn | scikit-learn | api.py, bahanbaku.py, prediction.py, prediksibaku.py |
| pandas | pandas | all files |
| matplotlib | matplotlib | all files |
| seaborn | seaborn | api.py, bahanbaku.py |
| prophet | prophet | prediction.py, prediksibaku.py |
| python-dotenv | python-dotenv | api.py |
| numpy | numpy | all files |
| mlxtend | mlxtend | association.py |

### Key findings
- `psycopg2-binary` is the pip package name, not `psycopg2`
- `sklearn` is the import name but `scikit-learn` is the pip package
- `dotenv` is the import name but `python-dotenv` is the pip package
- `uvicorn` is not directly imported in any file but is required to run FastAPI
- All version constraints use `>=` (not exact pins)
- 11 packages total, all verified against actual import statements
