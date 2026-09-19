FROM python:3.12-slim

# Build tools needed to compile prophet/scipy wheels when no prebuilt wheel matches.
RUN apt-get update \
    && apt-get install -y --no-install-recommends build-essential \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /app

COPY requirements.txt .
RUN pip install --no-cache-dir -r requirements.txt

COPY datamining/ ./datamining/

EXPOSE 8001

CMD ["uvicorn", "datamining.api:app", "--host", "0.0.0.0", "--port", "8001"]
