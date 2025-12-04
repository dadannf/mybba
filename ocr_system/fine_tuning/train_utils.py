"""
=============================================
Fine-tuning Utilities (Optional)
Train custom model untuk domain spesifik bank Indonesia
=============================================
"""

import os
import json
from typing import List, Dict, Tuple
import cv2
import numpy as np
from pathlib import Path
from loguru import logger


class FineTuningDataPreparer:
    """
    Prepare dataset untuk fine-tuning PaddleOCR
    untuk domain bank Indonesia
    """
    
    def __init__(self, dataset_dir: str = "./fine_tuning/dataset"):
        self.dataset_dir = Path(dataset_dir)
        self.images_dir = self.dataset_dir / "images"
        self.labels_dir = self.dataset_dir / "labels"
        
        # Create directories
        self.images_dir.mkdir(parents=True, exist_ok=True)
        self.labels_dir.mkdir(parents=True, exist_ok=True)
    
    def create_detection_annotation(self,
                                   image_path: str,
                                   bboxes: List[List[List[int]]],
                                   texts: List[str]) -> Dict:
        """
        Create annotation for detection model (DBNet)
        Format: {image_path: [[bbox, text], ...]}
        """
        annotations = []
        for bbox, text in zip(bboxes, texts):
            annotations.append({
                'transcription': text,
                'points': bbox,
                'difficult': False
            })
        
        return {
            'image_path': image_path,
            'annotations': annotations
        }
    
    def create_recognition_annotation(self,
                                     image_path: str,
                                     text: str) -> str:
        """
        Create annotation for recognition model (CRNN)
        Format: image_path\ttext
        """
        return f"{image_path}\t{text}\n"
    
    def save_detection_labels(self, annotations: List[Dict], output_file: str):
        """Save detection annotations to file"""
        with open(output_file, 'w', encoding='utf-8') as f:
            for ann in annotations:
                json.dump(ann, f, ensure_ascii=False)
                f.write('\n')
        logger.info(f"Detection labels saved to {output_file}")
    
    def save_recognition_labels(self, annotations: List[str], output_file: str):
        """Save recognition annotations to file"""
        with open(output_file, 'w', encoding='utf-8') as f:
            f.writelines(annotations)
        logger.info(f"Recognition labels saved to {output_file}")
    
    def augment_image(self, image: np.ndarray, num_augments: int = 3) -> List[np.ndarray]:
        """
        Augment image untuk increase dataset size
        - Rotation
        - Brightness
        - Blur
        - Noise
        """
        augmented = []
        
        for _ in range(num_augments):
            aug_img = image.copy()
            
            # Random rotation (-5 to 5 degrees)
            angle = np.random.uniform(-5, 5)
            h, w = aug_img.shape[:2]
            M = cv2.getRotationMatrix2D((w/2, h/2), angle, 1.0)
            aug_img = cv2.warpAffine(aug_img, M, (w, h))
            
            # Random brightness
            brightness = np.random.uniform(0.8, 1.2)
            aug_img = cv2.convertScaleAbs(aug_img, alpha=brightness, beta=0)
            
            # Random blur
            if np.random.random() > 0.5:
                kernel_size = np.random.choice([3, 5])
                aug_img = cv2.GaussianBlur(aug_img, (kernel_size, kernel_size), 0)
            
            # Random noise
            if np.random.random() > 0.5:
                noise = np.random.randint(0, 25, aug_img.shape, dtype=np.uint8)
                aug_img = cv2.add(aug_img, noise)
            
            augmented.append(aug_img)
        
        return augmented
    
    def prepare_transfer_receipt_dataset(self,
                                        source_images: List[str],
                                        labels: List[Dict]):
        """
        Prepare dataset khusus untuk bukti transfer
        
        Args:
            source_images: list of image paths
            labels: list of dicts dengan keys: bboxes, texts
        """
        logger.info("Preparing transfer receipt dataset...")
        
        detection_annotations = []
        recognition_annotations = []
        
        for idx, (img_path, label) in enumerate(zip(source_images, labels)):
            # Load image
            image = cv2.imread(img_path)
            if image is None:
                logger.warning(f"Failed to load {img_path}")
                continue
            
            # Save original
            img_name = f"receipt_{idx:04d}.jpg"
            save_path = self.images_dir / img_name
            cv2.imwrite(str(save_path), image)
            
            # Create detection annotation
            det_ann = self.create_detection_annotation(
                str(save_path),
                label['bboxes'],
                label['texts']
            )
            detection_annotations.append(det_ann)
            
            # Create recognition annotations for each text region
            for bbox, text in zip(label['bboxes'], label['texts']):
                # Crop region
                points = np.array(bbox, dtype=np.int32)
                x_min = int(min(points[:, 0]))
                x_max = int(max(points[:, 0]))
                y_min = int(min(points[:, 1]))
                y_max = int(max(points[:, 1]))
                
                cropped = image[y_min:y_max, x_min:x_max]
                
                # Save cropped region
                crop_name = f"receipt_{idx:04d}_crop_{len(recognition_annotations)}.jpg"
                crop_path = self.images_dir / crop_name
                cv2.imwrite(str(crop_path), cropped)
                
                # Add recognition annotation
                rec_ann = self.create_recognition_annotation(str(crop_path), text)
                recognition_annotations.append(rec_ann)
            
            # Augment
            augmented_images = self.augment_image(image, num_augments=2)
            for aug_idx, aug_img in enumerate(augmented_images):
                aug_name = f"receipt_{idx:04d}_aug_{aug_idx}.jpg"
                aug_path = self.images_dir / aug_name
                cv2.imwrite(str(aug_path), aug_img)
                
                # Add augmented annotations
                aug_det_ann = self.create_detection_annotation(
                    str(aug_path),
                    label['bboxes'],
                    label['texts']
                )
                detection_annotations.append(aug_det_ann)
        
        # Save labels
        self.save_detection_labels(
            detection_annotations,
            str(self.labels_dir / "det_train.txt")
        )
        self.save_recognition_labels(
            recognition_annotations,
            str(self.labels_dir / "rec_train.txt")
        )
        
        logger.info(f"Dataset prepared: {len(detection_annotations)} images for detection, "
                   f"{len(recognition_annotations)} crops for recognition")


class ModelTrainer:
    """
    Train custom PaddleOCR models
    Requires PaddleOCR training framework
    """
    
    def __init__(self, config_dir: str = "./fine_tuning/configs"):
        self.config_dir = Path(config_dir)
        self.config_dir.mkdir(parents=True, exist_ok=True)
    
    def create_detection_config(self, dataset_path: str, output_path: str):
        """
        Create configuration file for DBNet detection training
        """
        config = {
            "Global": {
                "use_gpu": True,
                "epoch_num": 100,
                "save_epoch_step": 10,
                "save_model_dir": "./output/det_custom/",
                "pretrained_model": "./pretrain_models/det_mv3_db_v2.0_train/best_accuracy",
            },
            "Architecture": {
                "model_type": "det",
                "algorithm": "DB",
                "Transform": None,
                "Backbone": {
                    "name": "MobileNetV3",
                    "scale": 0.5,
                    "model_name": "large"
                },
                "Neck": {
                    "name": "DBFPN",
                    "out_channels": 256
                },
                "Head": {
                    "name": "DBHead",
                    "k": 50
                }
            },
            "Train": {
                "dataset": {
                    "name": "SimpleDataSet",
                    "data_dir": dataset_path,
                    "label_file_list": ["labels/det_train.txt"],
                }
            }
        }
        
        config_path = self.config_dir / "det_custom.yml"
        with open(config_path, 'w') as f:
            json.dump(config, f, indent=2)
        
        logger.info(f"Detection config saved to {config_path}")
        return str(config_path)
    
    def create_recognition_config(self, dataset_path: str, output_path: str):
        """
        Create configuration file for CRNN recognition training
        """
        config = {
            "Global": {
                "use_gpu": True,
                "epoch_num": 100,
                "save_epoch_step": 10,
                "save_model_dir": "./output/rec_custom/",
                "pretrained_model": "./pretrain_models/rec_mv3_none_bilstm_ctc_v2.0_train/best_accuracy",
            },
            "Architecture": {
                "model_type": "rec",
                "algorithm": "CRNN",
                "Transform": None,
                "Backbone": {
                    "name": "MobileNetV1Enhance",
                    "scale": 0.5
                },
                "Neck": {
                    "name": "SequenceEncoder",
                    "encoder_type": "rnn"
                },
                "Head": {
                    "name": "CTCHead",
                }
            },
            "Train": {
                "dataset": {
                    "name": "SimpleDataSet",
                    "data_dir": dataset_path,
                    "label_file_list": ["labels/rec_train.txt"],
                }
            }
        }
        
        config_path = self.config_dir / "rec_custom.yml"
        with open(config_path, 'w') as f:
            json.dump(config, f, indent=2)
        
        logger.info(f"Recognition config saved to {config_path}")
        return str(config_path)
    
    def train_command_detection(self, config_path: str) -> str:
        """
        Generate training command for detection model
        
        Usage:
            Run this command in terminal:
            python PaddleOCR/tools/train.py -c {config_path}
        """
        return f"python PaddleOCR/tools/train.py -c {config_path}"
    
    def train_command_recognition(self, config_path: str) -> str:
        """
        Generate training command for recognition model
        """
        return f"python PaddleOCR/tools/train.py -c {config_path}"


# Example usage
if __name__ == "__main__":
    # Prepare dataset
    preparer = FineTuningDataPreparer()
    
    # Example: prepare dataset from labeled images
    # source_images = ["path/to/receipt1.jpg", "path/to/receipt2.jpg"]
    # labels = [
    #     {
    #         'bboxes': [[[10, 10], [100, 10], [100, 30], [10, 30]]],
    #         'texts': ['BCA']
    #     },
    #     ...
    # ]
    # preparer.prepare_transfer_receipt_dataset(source_images, labels)
    
    # Create training configs
    trainer = ModelTrainer()
    det_config = trainer.create_detection_config("./fine_tuning/dataset", "./output/det_custom")
    rec_config = trainer.create_recognition_config("./fine_tuning/dataset", "./output/rec_custom")
    
    print("Detection training command:")
    print(trainer.train_command_detection(det_config))
    print("\nRecognition training command:")
    print(trainer.train_command_recognition(rec_config))
