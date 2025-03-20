import os
import shutil

# Define dataset paths
source_train_dir = "./fruits360/fruits-360_100x100/fruits-360/Training"
source_test_dir = "./fruits360/fruits-360_100x100/fruits-360/Test"

target_dir = "./fruits10"  # All images will be copied here

# Select 10 fruit/vegetable categories
categories = [
    'Lemon 1', 'Pineaple 1', 'Strawberry 1', 'Salak 1', 
    'Watermelon 1', 'Rambutan 1', 'Banana 3', 'Onion Red 1', 'Blueberry 1', 'Apricot 1'
]

# Ensure target directory exists
os.makedirs(target_dir, exist_ok=True)

# Copy images from both Training and Test into fruits10/
for category in categories:
    src_train = os.path.join(source_train_dir, category)
    src_test = os.path.join(source_test_dir, category)
    dst_category = os.path.join(target_dir, category)

    # Ensure the category folder exists in the target directory
    os.makedirs(dst_category, exist_ok=True)

    # Copy training images if category exists
    if os.path.exists(src_train):
        shutil.copytree(src_train, dst_category, dirs_exist_ok=True)
        print(f"✅ Copied training images for: {category}")
    else:
        print(f"⚠️ WARNING: Training images for '{category}' not found!")

    # Copy testing images if category exists
    if os.path.exists(src_test):
        shutil.copytree(src_test, dst_category, dirs_exist_ok=True)
        print(f"✅ Copied testing images for: {category}")
    else:
        print(f"⚠️ WARNING: Testing images for '{category}' not found!")

print("🎉 Done! All selected categories have been copied into 'fruits10/'.")
