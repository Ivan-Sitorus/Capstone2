FROM python:3.13-slim

WORKDIR /app

COPY requirements.txt .

# Build tools needed to compile prophet/scipy wheels when no prebuilt wheel matches.
RUN apt-get update \
    && apt-get install -y --no-install-recommends build-essential \
    && pip install --no-cache-dir -r requirements.txt \
    && apt-get purge -y --auto-remove build-essential \
    && rm -rf /var/lib/apt/lists/*

COPY datamining/ ./datamining/

EXPOSE 8001

CMD ["uvicorn", "datamining.api:app", "--host", "0.0.0.0", "--port", "8001"]
