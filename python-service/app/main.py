from datetime import datetime, timezone
from fastapi import FastAPI 
from pydantic import BaseModel 

SERVICE_NAME = "supportdesk-python"
SERVICE_VERSION = "0.1.0"

app = FastAPI(
    title = "Support Desk Ticket Intelligence",
    version = SERVICE_VERSION,
    description = "Rule based ticket analysis service for support desk tickets",
)

class HealthResponse(BaseModel):
    status: str
    service: str
    version: str
    timestamp: datetime

@app.get("/health", response_model=HealthResponse, tags=["system"])
def health() -> HealthResponse:
    """
    Health check endpoint for the service.
    Returns the current status, service name, version, and timestamp.
    """
    return HealthResponse(
        status="healthy",
        service=SERVICE_NAME,
        version=SERVICE_VERSION,
        timestamp=datetime.now(timezone.utc)
    )

