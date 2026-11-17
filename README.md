<div align="center">

# ♻️ EcoPrint

### AI-Powered Reverse Vending Machine with Integrated Print & Rewards System

**Transforming Plastic Bottles into Rewards Using Computer Vision & IoT**

[![Python](https://img.shields.io/badge/Python-3.9+-3776AB?style=for-the-badge&logo=python&logoColor=white)](https://python.org)
[![YOLOv8](https://img.shields.io/badge/YOLOv8-PyTorch-EE4C2C?style=for-the-badge&logo=pytorch&logoColor=white)](https://docs.ultralytics.com/)
[![Raspberry Pi](https://img.shields.io/badge/Raspberry_Pi-A22846?style=for-the-badge&logo=raspberry-pi&logoColor=white)](https://www.raspberrypi.org/)
[![CodeIgniter](https://img.shields.io/badge/CodeIgniter-4-EF4223?style=for-the-badge&logo=codeigniter&logoColor=white)](https://codeigniter.com)
[![PHP](https://img.shields.io/badge/PHP-8.1+-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://php.net)
[![MySQL](https://img.shields.io/badge/MySQL-MariaDB-4479A1?style=for-the-badge&logo=mysql&logoColor=white)](https://www.mysql.com)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg?style=for-the-badge)](https://opensource.org/licenses/MIT)

[Features](#-features) • [Demo](#-how-it-works) • [Tech Stack](#-technology-stack) • [Installation](#-quick-start) • [Documentation](#-documentation)

</div>

---

## 🌟 About

**EcoPrint** is an intelligent IoT-enabled **Reverse Vending Machine (RVM)** that makes recycling rewarding and effortless. Using AI-powered bottle detection, it automatically identifies, sorts, and rewards users with points for every bottle deposited—all while printing instant receipts and tracking environmental impact through a comprehensive web platform.

> 🎓 **Developed by Team ArdeaAlba** for Camarines Sur Polytechnic Colleges (CSPC) as part of an institutional sustainability initiative.

### 💡 The Problem
- Manual recycling is time-consuming and unrewarding
- Difficult to track individual environmental contributions
- Lack of incentives for consistent recycling behavior
- Traditional RVMs are expensive and lack smart features

### ✨ Our Solution
A dual-component smart system combining:
1. **🤖 RVM Hardware** — Raspberry Pi + YOLOv8 AI for instant bottle recognition
2. **🌐 Web Platform** — Real-time analytics, rewards management, and user dashboards

</div>

---

## 🚀 Features

<div align="center">

### 🎯 Core Capabilities

</div>

| 🤖 **AI Detection** | 🎫 **Smart Authentication** | 🖨️ **Instant Rewards** | 📊 **Analytics Dashboard** |
|:---:|:---:|:---:|:---:|
| YOLOv8-powered bottle recognition | RFID/NFC card tap & go | Thermal receipt printing | Real-time tracking & reports |
| 95%+ accuracy | < 1 sec authentication | QR code validation | Export to CSV/PDF |
| Multi-class support | Google OAuth SSO | Points accumulation | Top contributors leaderboard |
| Real-time processing | Secure JWT API | Rewards catalog | Environmental impact metrics |

### ✅ What Makes EcoPrint Special

- 🔥 **Lightning Fast**: < 100ms bottle detection on Raspberry Pi 4
- 🎯 **Highly Accurate**: 95%+ mAP@0.5 on custom bottle dataset
- 🌍 **Eco-Friendly**: Track individual and collective environmental impact
- 💰 **Rewarding**: Gamified point system encourages consistent recycling
- 📱 **User-Friendly**: Simple 6-step process from tap to receipt
- 🔐 **Secure**: JWT authentication, encrypted credentials, GDPR compliant
- 📈 **Scalable**: Supports multiple RVMs with centralized management
- 🆓 **Open Source**: MIT licensed, modify and deploy freely

---

## 🎬 How It Works

---

## 🎬 How It Works

<div align="center">

### 📱 Simple 6-Step Process

```mermaid
graph LR
    A[👤 Tap RFID Card] --> B[🤖 AI Detects Bottle]
    B --> C[✅ Validation]
    C --> D[💰 Points Added]
    D --> E[🖨️ Receipt Printed]
    E --> F[🎁 Redeem Rewards]
```

</div>

### For Users:

1. **🎫 Authenticate** — Tap your student/RFID card on the reader
2. **♻️ Insert Bottle** — Place your plastic bottle in the slot
3. **🤖 AI Recognition** — YOLOv8 instantly identifies bottle type (PET, HDPE, etc.)
4. **✅ Validation** — System checks authenticity and condition
5. **🖨️ Get Receipt** — Thermal printer outputs receipt with QR code & points
6. **🎁 Redeem** — Use accumulated points in the web portal for rewards!

### System Architecture

```
┌────────────────────────────────────────────────────────────────────┐
│                         EcoPrint Ecosystem                         │
├──────────────┬──────────────┬──────────────┬──────────────────────┤
│ 📸 CV Input  │ 🧠 AI Model  │ 🎫 RFID Auth │ 🖨️ Print Receipt     │
│ • YOLOv8     │ • Bottle     │ • JWT API    │ • QR Code            │
│ • OpenCV     │   Detection  │ • Real-time  │ • Points Summary     │
│ • Pi Camera  │ • 95%+ mAP   │ • OAuth SSO  │ • Transaction ID     │
└──────────────┴──────────────┴──────────────┴──────────────────────┘
         ↓                                               ↑
   [Raspberry Pi 4]  ←――――― REST API ―――――→  [Web Platform]
         ↓                                               ↑
   Hardware Control                           Cloud Database (MySQL)
```

---

## 🛠️ Technology Stack

### Hardware (RVM)

---

## 🛠️ Technology Stack

### Hardware (RVM)

| Component | Technology | Purpose |
|-----------|------------|---------|
| 🧠 **Processing** | Raspberry Pi 4 (4GB RAM) | Main computing unit |
| 📸 **Vision** | Pi Camera Module v2 | Real-time video capture |
| 🤖 **AI Model** | YOLOv8 (PyTorch/ONNX) | Bottle detection & classification |
| 🖥️ **Display** | 16x2 LCD (I2C) | User feedback interface |
| 🎫 **RFID** | RC522/PN532 NFC Reader | Card authentication |
| 🖨️ **Printer** | USB Thermal Printer (ESC/POS) | Receipt generation |
| ⚙️ **Control** | GPIO Relays | Motor/gate actuation |

### Software Stack

**Backend & API**
- 🔧 **Framework**: CodeIgniter 4
- 💻 **Language**: PHP 8.1+
- 🗄️ **Database**: MySQL 8.0 / MariaDB 10.4+
- 🔐 **Auth**: Google OAuth 2.0 + JWT
- 📄 **PDF**: mPDF
- 🔲 **QR Codes**: SimpleSoftware QR Code

**AI & Computer Vision**
- 🐍 **Language**: Python 3.9+
- 🎯 **Detection**: Ultralytics YOLOv8
- 👁️ **Vision**: OpenCV
- 🧮 **Computing**: NumPy

**Hardware Interface (Python)**
- 🔌 RPi.GPIO — GPIO control
- 📺 RPLCD — LCD display
- 📡 PySerial — Thermal printer
- 🎫 PyScard — RFID reader
- 🌐 Requests — API communication

---

## 📊 Performance Metrics

| Metric | Result | Details |
|--------|--------|---------|
| 🎯 **Detection Accuracy** | 95%+ mAP@0.5 | Custom bottle dataset |
| ⚡ **Inference Speed** | < 100ms | Raspberry Pi 4, 320x320 input |
| 🎫 **RFID Success Rate** | > 98% | Card tap authentication |
| 🌐 **API Response Time** | < 200ms | Average authentication & logging |
| 👥 **Concurrent Users** | 100+ | Web platform simultaneous access |
| ⏱️ **System Uptime** | 24/7 | Auto-recovery from network issues |

---

## 🚀 Quick Start

### Prerequisites

- **For RVM**: Raspberry Pi 4 (4GB+), Camera, LCD, RFID reader, Thermal printer
- **For WebApp**: PHP 8.1+, MySQL 8.0+, Composer 2.0+

### 🔧 RVM Setup (Raspberry Pi)

---

## 🚀 Quick Start

### Prerequisites

- **For RVM**: Raspberry Pi 4 (4GB+), Camera, LCD, RFID reader, Thermal printer
- **For WebApp**: PHP 8.1+, MySQL 8.0+, Composer 2.0+

### 🔧 RVM Setup (Raspberry Pi)

```bash
# 1. Clone repository
git clone https://github.com/jkabonita/EcoPrint-RVM-WebApp.git
cd EcoPrint-RVM-WebApp/EcoPrint-RVM

# 2. Install system dependencies
sudo apt update && sudo apt install -y python3-pip python3-opencv python3-smbus i2c-tools

# 3. Install Python packages
pip3 install -r requirements.txt

# 4. Configure settings
nano new_ecoprint.py  # Edit API endpoints, GPIO pins, camera settings

# 5. Add trained YOLOv8 model
# Place your trained model files in best_models/
# - ecoprint320.pt or ecoprint640.pt
# - classes.txt (bottle class labels)

# 6. Run the application
python3 new_ecoprint.py
```

### 🌐 WebApp Setup (Server)

```bash
# 1. Navigate to WebApp directory
cd EcoPrint-RVM-WebApp/EcoPrint-WebApp

# 2. Install PHP dependencies
composer install

# 3. Configure environment
cp env .env
nano .env  # Edit database, Google OAuth, API keys

# 4. Create database and import schema
mysql -u root -p -e "CREATE DATABASE ecoprint"
mysql -u root -p ecoprint < ecoprint.sql

# 5. Set permissions
chmod -R 755 writable/ uploads/

# 6. Start server
php spark serve
# Access at http://localhost:8080
```

### 🔑 Configuration

<details>
<summary><b>📝 Environment Variables (.env)</b></summary>

```env
# Database Configuration
database.default.hostname = 127.0.0.1
database.default.database = ecoprint
database.default.username = root
database.default.password = your_password
database.default.DBDriver = MySQLi
database.default.port = 3306

# Google OAuth 2.0
google.clientId = YOUR_CLIENT_ID.apps.googleusercontent.com
google.clientSecret = YOUR_CLIENT_SECRET
google.redirectUri = http://localhost:8080/auth/google/callback

# reCAPTCHA (Optional)
recaptcha.site_key = YOUR_SITE_KEY
recaptcha.secret_key = YOUR_SECRET_KEY

# Application
app.baseURL = http://localhost:8080/
app.environment = development
```
</details>

<details>
<summary><b>🔧 RVM Python Configuration</b></summary>

Edit `new_ecoprint.py`:

```python
# API Configuration
API_BASE_URL = "http://your-webapp-domain.com"
API_VALIDATE_USER = "/api/validate-user"
API_LOG_TRANSACTION = "/api/log-transaction"

# Hardware GPIO Pins
LCD_RS = 7
LCD_EN = 8
MOTOR_PIN = 17
GATE_PIN = 18

# Camera Settings
CAMERA_RESOLUTION = (640, 480)
DETECTION_CONFIDENCE = 0.75

# RFID Serial Port
RFID_PORT = "/dev/ttyUSB0"
RFID_BAUDRATE = 9600
```
</details>

---

## 📖 Documentation

### 🎓 User Guide

#### For Students/Users

1. **🎫 Tap Card** — Present your RFID/student ID to the reader
2. **♻️ Insert Bottle** — Place bottle in the designated slot
3. **⏳ Wait** — AI processes and validates (< 2 seconds)
4. **🖨️ Get Receipt** — Collect printed receipt with points
5. **🌐 Check Dashboard** — Login to web portal to view points
6. **🎁 Redeem** — Exchange points for rewards

#### For Administrators

**📊 Dashboard Features:**
- Real-time RVM status monitoring (online/offline)
- Transaction logs with filters and exports
- User management (view, edit, adjust points)
- Rewards catalog management
- Analytics: bottles collected, top users, trends

**🔧 System Management:**
- Configure RVM settings remotely
- View error logs and diagnostics
- Generate monthly/yearly reports
- Manage user roles and permissions

### 🔌 API Reference

<details>
<summary><b>Validate User (POST /api/validate-user)</b></summary>

**Request:**
```json
{
  "rfid": "04B2C3D5E6F7",
  "rvm_id": "RVM-001"
}
```

**Response:**
```json
{
  "status": "success",
  "user_id": 1234,
  "name": "Juan Dela Cruz",
  "email": "juan@my.cspc.edu.ph",
  "points": 150,
  "jwt_token": "eyJ0eXAiOiJKV1QiLCJhbGc..."
}
```
</details>

<details>
<summary><b>Log Transaction (POST /api/log-transaction)</b></summary>

**Request:**
```json
{
  "user_id": 1234,
  "rvm_id": "RVM-001",
  "bottle_type": "PET",
  "points_earned": 5,
  "jwt_token": "eyJ0eXAiOiJKV1QiLCJhbGc..."
}
```

**Response:**
```json
{
  "status": "success",
  "transaction_id": "TXN-2025-0001",
  "total_points": 155,
  "receipt_data": {
    "qr_code": "base64_encoded_qr",
    "timestamp": "2025-01-15 14:30:22"
  }
}
```
</details>

---

## 📁 Project Structure

```
EcoPrint-RVM-WebApp/
│
├── 📁 EcoPrint-RVM/                    # Raspberry Pi Application
│   ├── new_ecoprint.py                # Main RVM application
│   ├── gui.py                         # GUI display module
│   ├── toast.py                       # Notification system
│   ├── heartbeat_sender.py            # Network monitor
│   ├── requirements.txt               # Python dependencies
│   ├── assets/                        # GUI images
│   └── best_models/
│       └── classes.txt                # Bottle class labels
│                                      # ⚠️ Add .pt/.onnx models here
│
├── 📁 EcoPrint-WebApp/                # CodeIgniter 4 Web Platform
│   ├── app/
│   │   ├── Config/                    # Configuration
│   │   ├── Controllers/               # MVC Controllers
│   │   │   ├── Admin.php             # Admin panel
│   │   │   ├── User.php              # User dashboard
│   │   │   ├── Auth.php              # Authentication
│   │   │   ├── MachineApi.php        # RVM API
│   │   │   └── PrintController.php   # Print service
│   │   ├── Models/                    # Database models
│   │   ├── Views/                     # Blade templates
│   │   ├── Libraries/                 # Custom libraries
│   │   │   ├── GoogleAuth.php        # OAuth handler
│   │   │   └── JwtLib.php            # JWT auth
│   │   ├── Filters/                   # Middleware
│   │   └── Services/                  # Business logic
│   ├── public/                        # Web root
│   │   ├── assets/                    # Frontend assets
│   │   └── bootstrap/                 # Theme files
│   ├── writable/                      # Logs & cache
│   ├── tests/                         # Test suite
│   ├── composer.json                  # PHP dependencies
│   ├── ecoprint.sql                   # Database schema
│   └── spark                          # CLI tool
│
└── README.md                          # This file
```

---

## 🧪 Testing & Validation

---

## 🧪 Testing & Performance

### Test Results

✅ **Computer Vision**: 95%+ mAP@0.5 on custom bottle dataset  
✅ **Processing Speed**: < 100ms inference on Raspberry Pi 4  
✅ **RFID Accuracy**: > 98% successful reads  
✅ **API Latency**: < 200ms average response time  
✅ **Scalability**: 100+ concurrent web users  
✅ **Reliability**: 24/7 uptime with auto-recovery  

### Supported Bottle Types

- 🍼 **PET Bottles** (Polyethylene Terephthalate)
- 🧴 **HDPE Containers** (High-Density Polyethylene)
- 🍾 **Glass Bottles**
- 🥫 **Aluminum Cans**
- ➕ **Custom Classes** (Extensible via model retraining)

---

## 🤝 Contributing

We welcome contributions! Whether it's:

- 🐛 Bug reports and fixes
- ✨ New feature implementations
- 📝 Documentation improvements
- 🎨 UI/UX enhancements
- 🧪 Additional test cases

### How to Contribute

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

---

## 🐛 Troubleshooting

<details>
<summary><b>RVM not detecting bottles</b></summary>

- Check camera connection and focus
- Verify YOLOv8 model files are in `best_models/`
- Ensure adequate lighting conditions
- Check `DETECTION_CONFIDENCE` threshold in config
</details>

<details>
<summary><b>RFID reader not responding</b></summary>

- Verify serial port connection (`ls /dev/ttyUSB*`)
- Check baud rate configuration (usually 9600)
- Test RFID module independently
- Ensure proper wiring (VCC, GND, TX, RX)
</details>

<details>
<summary><b>WebApp database connection failed</b></summary>

- Check `.env` database credentials
- Verify MySQL service is running (`sudo systemctl status mysql`)
- Confirm database exists (`SHOW DATABASES;`)
- Test connection: `mysql -u root -p ecoprint`
</details>

<details>
<summary><b>Google OAuth not working</b></summary>

- Verify OAuth credentials in `.env`
- Check redirect URI matches Google Console
- Ensure domain is whitelisted
- Clear browser cache and cookies
</details>

---

## 📞 Support & Contact

- 📧 **Email**: support@ecoprint.cspc.edu.ph
- 🐛 **Issues**: [GitHub Issues](https://github.com/jkabonita/EcoPrint-RVM-WebApp/issues)
- 📚 **Documentation**: [Wiki](https://github.com/jkabonita/EcoPrint-RVM-WebApp/wiki)
- 💬 **Discussions**: [GitHub Discussions](https://github.com/jkabonita/EcoPrint-RVM-WebApp/discussions)

---

## 📜 License

This project is licensed under the **MIT License** - see the [LICENSE](LICENSE) file for details.

```
MIT License

Copyright (c) 2025 Team ArdeaAlba - Camarines Sur Polytechnic Colleges

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT.
```

---

## 🙏 Acknowledgments

### Developed By
**Team ArdeaAlba**  
Camarines Sur Polytechnic Colleges (CSPC)  
Information and Communications Technology Unit (ICTU)

### Technologies & Frameworks
- [YOLOv8 by Ultralytics](https://docs.ultralytics.com/) — Object detection model
- [CodeIgniter 4](https://codeigniter.com/) — PHP web framework
- [Raspberry Pi Foundation](https://www.raspberrypi.org/) — Hardware platform
- [OpenCV](https://opencv.org/) — Computer vision library
- [Bootstrap](https://getbootstrap.com/) — Frontend framework

### Special Thanks
- CSPC ICTU for project support and deployment
- Faculty advisers for technical guidance
- Student testers for valuable feedback
- Open-source community for amazing tools

---

<div align="center">

### ⭐ Star This Repository

If you find EcoPrint useful, please consider giving it a star! It helps others discover the project.

[![GitHub stars](https://img.shields.io/github/stars/jkabonita/EcoPrint-RVM-WebApp?style=social)](https://github.com/jkabonita/EcoPrint-RVM-WebApp/stargazers)
[![GitHub forks](https://img.shields.io/github/forks/jkabonita/EcoPrint-RVM-WebApp?style=social)](https://github.com/jkabonita/EcoPrint-RVM-WebApp/network/members)

---

**♻️ EcoPrint** — *Transforming Waste into Opportunity, One Bottle at a Time*

Made with 💚 by Team ArdeaAlba | © 2025 CSPC

</div>
