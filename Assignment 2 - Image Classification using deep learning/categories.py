import os
import shutil

source_train_dir = "./datasets/fruits360/fruits-360/Training"
source_test_dir = "./datasets/fruits360/fruits-360/Test"

target_base = "./datasets/fruits10"
train_dir = os.path.join(target_base, "train")
test_dir = os.path.join(target_base, "test")

categories = [
    'Golden', 'Blueberry', 'Watermelon', 'Avocado', 
    'Kiwi', 'Beans', 'Carrot', 'Cauliflower', 'Dates', 'Papaya'
]

# Create directories
for category in categories:
    os.makedirs(os.path.join(train_dir, category), exist_ok=True)
    os.makedirs(os.path.join(test_dir, category), exist_ok=True)

# Copy selected categories' images
for category in categories:
    # Copy training images
    src_train = os.path.join(source_train_dir, category)
    dst_train = os.path.join(train_dir, category)
    shutil.copytree(src_train, dst_train, dirs_exist_ok=True)
    
    # Copy testing images
    src_test = os.path.join(source_test_dir, category)
    dst_test = os.path.join(test_dir, category)
    shutil.copytree(src_test, dst_test, dirs_exist_ok=True)

print("✅ Selected 10 categories successfully copied!")
