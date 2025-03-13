# Fruits-360 Dataset Setup Guide

## 📌 How to Replicate This Setup

This guide will help you download and set up the **Fruits-360 dataset** using Kaggle's API. 

---

## ✅ **Step 1: Install Kaggle CLI**
First, you need to install Kaggle in your Python environment:

```bash
pip install kaggle
```

---

## ✅ **Step 2: Get Your Kaggle API Token**
1. Go to [Kaggle](https://www.kaggle.com/).
2. Click on your **profile picture** (top-right corner).
3. Go to **Account Settings**.
4. Scroll down to the **API** section and click **Create New API Token**.
5. This will download a file called **`kaggle.json`**.

---

## ✅ **Step 3: Move `kaggle.json` to the Correct Location**
Now, move `kaggle.json` to the required directory so that the Kaggle CLI can use it:

```bash
mkdir -p ~/.kaggle
mv ~/Downloads/kaggle.json ~/.kaggle/
chmod 600 ~/.kaggle/kaggle.json
```

---

## 🤔 **Why Are We Doing This?**
We're moving `kaggle.json` to `~/.kaggle/` because the Kaggle CLI **needs it to authenticate**. Without it, Kaggle won’t allow us to download datasets. The `chmod 600` command ensures that **only you can access the file**, keeping your API credentials secure.

---

## ✅ **Step 4: Verify Kaggle CLI is Working**
To confirm everything is set up correctly, run:

```bash
kaggle datasets list
```

If it worked, you should see a list of available datasets similar to this:

![alt text](image.png)

---

## ✅ **Step 5: Download the Fruits-360 Dataset**
Now, download the dataset to your preferred location:

```bash
mkdir -p ~/OneDrive/Desktop/ml/datasets/fruits360
kaggle datasets download moltean/fruits -p ~/OneDrive/Desktop/ml/datasets/fruits360
unzip ~/OneDrive/Desktop/ml/datasets/fruits360/fruits.zip -d ~/OneDrive/Desktop/ml/datasets/fruits360
```
---
![alt text](image-1.png)

## 🎯 **Final Check**

Run:
```bash
ls ~/OneDrive/Desktop/ml/datasets/fruits360
```
You should see:
```
fruits-360/
  ├── Training/
  ├── Test/
```

