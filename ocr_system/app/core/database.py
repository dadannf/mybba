"""
=============================================
Database Connection Module
SQLAlchemy Setup untuk MySQL
=============================================
"""

from sqlalchemy import create_engine, Column, Integer, String, Float, DateTime, Text, Enum
from sqlalchemy.ext.declarative import declarative_base
from sqlalchemy.orm import sessionmaker
from datetime import datetime
from app.core.config import settings
import enum

# Create engine
engine = create_engine(
    settings.database_url,
    pool_pre_ping=True,
    pool_size=10,
    max_overflow=20,
    echo=settings.debug
)

# Session factory
SessionLocal = sessionmaker(autocommit=False, autoflush=False, bind=engine)

# Base class
Base = declarative_base()


class ValidationStatus(str, enum.Enum):
    """Validation status enum"""
    PENDING = "pending"
    APPROVED = "approved"
    REJECTED = "rejected"


class OCRValidation(Base):
    """Model untuk menyimpan hasil validasi OCR"""
    __tablename__ = "ocr_validations"
    
    id = Column(Integer, primary_key=True, index=True, autoincrement=True)
    
    # Upload info
    filename = Column(String(255), nullable=False)
    file_path = Column(String(500), nullable=False)
    upload_timestamp = Column(DateTime, default=datetime.now, nullable=False)
    
    # User info (admin/siswa yang upload)
    uploader_type = Column(Enum('admin', 'siswa'), nullable=False)
    uploader_id = Column(String(50), nullable=False)  # username admin atau NIS siswa
    
    # OCR Results
    raw_text = Column(Text, nullable=True)
    detected_boxes = Column(Text, nullable=True)  # JSON string of bounding boxes
    
    # Parsed Information
    bank_name = Column(String(100), nullable=True)
    account_number = Column(String(50), nullable=True)
    account_name = Column(String(200), nullable=True)
    transfer_amount = Column(Float, nullable=True)
    transfer_date = Column(DateTime, nullable=True)
    reference_number = Column(String(100), nullable=True)
    
    # Expected Information (untuk validasi)
    expected_amount = Column(Float, nullable=True)
    expected_nis = Column(String(20), nullable=True)
    expected_nama = Column(String(200), nullable=True)
    keuangan_id = Column(Integer, nullable=True)
    
    # Confidence Scores
    overall_confidence = Column(Float, nullable=True)
    detection_confidence = Column(Float, nullable=True)
    recognition_confidence = Column(Float, nullable=True)
    amount_match_score = Column(Float, nullable=True)
    
    # Validation Result
    validation_status = Column(Enum('pending', 'approved', 'rejected'), default='pending', nullable=False)
    validation_message = Column(Text, nullable=True)
    auto_decision = Column(String(20), nullable=True)  # 'accept', 'reject', 'review'
    
    # Processing Info
    processing_time_ms = Column(Float, nullable=True)
    error_message = Column(Text, nullable=True)
    
    # Timestamps
    created_at = Column(DateTime, default=datetime.now, nullable=False)
    updated_at = Column(DateTime, default=datetime.now, onupdate=datetime.now, nullable=False)
    validated_at = Column(DateTime, nullable=True)
    validated_by = Column(String(50), nullable=True)


def get_db():
    """Dependency untuk mendapatkan database session"""
    db = SessionLocal()
    try:
        yield db
    finally:
        db.close()


def init_db():
    """Initialize database tables"""
    Base.metadata.create_all(bind=engine)
    print("Database tables created successfully!")


if __name__ == "__main__":
    init_db()
