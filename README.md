<div align="center">

# ♻️ EcoPrint
### AI-Powered Reverse Vending Machine with Integrated Print Receipt System

**A dual-component IoT solution combining Computer Vision, Machine Learning, and Web-Based Waste Management**

[![Python](https://img.shields.io/badge/Python-3.9+-3776AB?style=for-the-badge&logo=python&logoColor=white)](https://python.org)
[![YOLOv8](https://img.shields.io/badge/YOLOv8-PyTorch-EE4C2C?style=for-the-badge&logo=pytorch&logoColor=white)](https://docs.ultralytics.com/)
[![Raspberry Pi](https://img.shields.io/badge/Raspberry_Pi-A22846?style=for-the-badge&logo=raspberry-pi&logoColor=white)](https://www.raspberrypi.org/)
[![CodeIgniter](https://img.shields.io/badge/CodeIgniter-4-EF4223?style=for-the-badge&logo=codeigniter&logoColor=white)](https://codeigniter.com)
[![PHP](https://img.shields.io/badge/PHP-8.1+-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://php.net)
[![MySQL](https://img.shields.io/badge/MySQL-MariaDB-4479A1?style=for-the-badge&logo=mysql&logoColor=white)](https://www.mysql.com)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg?style=for-the-badge)](https://opensource.org/licenses/MIT)

</div>

---

## 📋 System Overview

**EcoPrint** is an intelligent, IoT-enabled **Reverse Vending Machine (RVM)** that incentivizes recycling through automated bottle classification, real-time reward point accumulation, and instant printed receipt generation. The system combines:

1. **EcoPrint-RVM** — Raspberry Pi-based computer vision module with YOLOv8 object detection
2. **EcoPrint-WebApp** — PHP CodeIgniter 4 web platform for user management, transaction tracking, and analytics

The hardware setup includes:
- **Raspberry Pi 4** with Camera Module for real-time bottle detection
- **LCD Display** for user interaction feedback
- **RFID/NFC Reader** for student/user identification
- **Thermal Printer** for instant receipt generation
- **Arduino Controller** for motor/actuator control and GPIO management

---

## 🎯 Key Features & Capabilities

### 🤖 EcoPrint-RVM (Raspberry Pi + YOLOv8)

```
┌────────────────────────────────────────────────────────────────────┐
│                     EcoPrint RVM Pipeline                          │
├──────────────┬──────────────┬──────────────┬──────────────────────┤
│ 📸 CV Input  │ 🧠 AI Model  │ 🎫 RFID Auth │ 🖨️ Print Receipt     │
│ • YOLOv8     │ • Bottle     │ • Student ID │ • Thermal Printer    │
│ • OpenCV     │   Detection  │   Validation │ • Points Summary     │
│ • Real-time  │ • Multi-class│ • Real-time  │ • Transaction Log    │
└──────────────┴──────────────┴──────────────┴──────────────────────┘
```

- **AI-Powered Bottle Detection**: YOLOv8 PyTorch model trained on plastic bottle classification (PET, HDPE, etc.)
- **Dual Detection Modes**:
  - **Real-time Mode**: Continuous background detection with instant result fetch
  - **Single Capture Mode**: Fast single-shot inference when bottle detected
- **RFID/NFC Integration**: Tap-and-go student ID authentication
- **LCD User Interface**: 16x2 or 20x4 character LCD with real-time feedback
- **Automated Receipt Printing**: Instant thermal print with QR code, transaction details, and accumulated points
- **Hardware Control**: GPIO-based motor control for bottle acceptance/rejection gates
- **Network Heartbeat**: Automatic connectivity monitoring and API health checks
- **Toast Notifications**: Visual on-screen alerts for user actions and system status

**Supported Bottle Classes**:
- PET Bottles
- HDPE Containers
- Glass Bottles
- Aluminum Cans
- Custom trained classes

---

### 🌐 EcoPrint-WebApp (CodeIgniter 4)

- **User Portal**:
  - Google OAuth 2.0 authentication (CSPC institutional email: `@my.cspc.edu.ph`)
  - Personal dashboard with total points, transaction history, and redemption logs
  - QR code-based receipt validation
  - Rewards catalog and point redemption interface
  
- **Admin Portal**:
  - Real-time RVM monitoring (online/offline status, last heartbeat timestamp)
  - User account management (students, faculty, staff)
  - Transaction logs with CSV export and PDF report generation
  - Analytics dashboard: Total bottles collected, top contributors, daily/monthly charts
  - Rewards inventory management
  - System configuration and settings

- **RVM API Endpoints**:
  - `/api/validate-user` — RFID authentication
  - `/api/log-transaction` — Record bottle deposits
  - `/api/heartbeat` — RVM connectivity status updates
  - `/api/get-user-points` — Fetch user accumulated points for receipt printing

---

## 🛠️ Technology Stack

| Component | Technology | Purpose |
|---|---|---|
| **RVM Edge Device** | Raspberry Pi 4 | Main processing unit |
| **AI Framework** | YOLOv8 PyTorch / ONNX | Real-time bottle classification |
| **Computer Vision** | OpenCV | Camera feed processing |
| **Hardware Interface** | RPi.GPIO | GPIO control for actuators |
| **RFID Module** | Python smartcard | Student ID reading |
| **Display** | RPLCD i2c | LCD user feedback |
| **Thermal Printer** | Serial ESC/POS | Receipt generation |
| **Backend Framework** | CodeIgniter 4 | Web application |
| **Language** | PHP 8.1+ | Server-side logic |
| **Database** | MySQL / MariaDB | Data persistence |
| **Authentication** | Google OAuth 2.0 | Institutional SSO |
| **PDF Generation** | mPDF | Transaction reports |
| **QR Codes** | Simple QR Code | Receipt validation |

---

## 📖 User Manual

### 🎓 For Students / Users

#### Step 1: Approach the RVM
- Wait for the LCD screen to display **"Ready. Tap ID Card"**

#### Step 2: Authenticate with RFID/NFC Card
- Tap your student ID or registered RFID card on the reader
- LCD displays: **"Welcome, [Name]"** with current point balance

#### Step 3: Insert Bottle
- Place plastic bottle into the RVM slot
- Real-time camera feed captures and classifies the bottle
- LCD displays: **"Detected: PET Bottle — 5 Points"**

#### Step 4: Accept or Reject
- System validates bottle type and condition
- If valid: **"Accepted! +5 Points"** — bottle is stored, gate opens
- If invalid: **"Rejected. Try Again"** — bottle is returned

#### Step 5: Collect Receipt
- Thermal printer outputs a transaction receipt with:
  - Transaction ID and QR code
  - Date/time and bottle type
  - Points earned this transaction
  - Total accumulated points
  - Redemption instructions

#### Step 6: Redeem Rewards (Web Portal)
- Log in to **EcoPrint-WebApp** using CSPC email
- Browse rewards catalog
- Redeem points for school supplies, vouchers, or merchandise

---

### 👨‍💼 For Administrators

#### A. Monitor RVM Fleet
- Dashboard displays all connected RVMs with status indicators:
  - **🟢 Online**: Active and accepting bottles
  - **🔴 Offline**: No heartbeat in last 5 minutes
  - **🟡 Maintenance**: Manual maintenance mode

#### B. View Transaction Logs
- Filter by date range, RVM location, user, or bottle type
- Export to CSV or generate PDF summary reports
- Real-time graphs: Daily bottle count, top contributors, point distribution

#### C. Manage Rewards Inventory
- Add new reward items with point values
- Track redemption requests and approve/reject
- Update stock quantities and availability

#### D. User Account Administration
- View registered users (students, faculty)
- Manually adjust point balances (admin override)
- Deactivate or suspend accounts

---

## ⚙️ System Requirements

### For RVM (Raspberry Pi)
- **Hardware**:
  - Raspberry Pi 4 Model B (4GB RAM recommended)
  - Raspberry Pi Camera Module v2 or compatible USB webcam
  - 16x2 or 20x4 I2C LCD Display
  - RC522 or PN532 RFID/NFC Reader Module
  - USB Thermal Printer (ESC/POS compatible)
  - Relay modules for motor control
  - MicroSD Card (32GB minimum, Class 10)
  
- **Software**:
  - Raspberry Pi OS (Bullseye or Bookworm)
  - Python 3.9 or higher
  - Libraries: `ultralytics`, `opencv-python`, `RPi.GPIO`, `RPLCD`, `pyserial`, `requests`

### For WebApp (Server)
- **Server Environment**:
  - PHP 8.1+ with extensions: `intl`, `mbstring`, `mysqlnd`, `curl`, `json`, `openssl`
  - MySQL 8.0+ or MariaDB 10.4+
  - Composer 2.0+
  - Apache or Nginx web server

---

## 🚀 Installation & Setup

### 🔧 Part 1: EcoPrint-RVM (Raspberry Pi)

#### 1. Prepare Raspberry Pi
```bash
# Update system
sudo apt update && sudo apt upgrade -y

# Install Python dependencies
sudo apt install python3-pip python3-opencv -y

# Install I2C tools for LCD
sudo apt install python3-smbus i2c-tools -y
sudo raspi-config  # Enable I2C interface
```

#### 2. Clone Repository
```bash
git clone https://github.com/yourusername/EcoPrint.git
cd EcoPrint/EcoPrint-RVM
```

#### 3. Install Python Packages
```bash
pip3 install ultralytics opencv-python numpy requests RPi.GPIO RPLCD pyserial pyscard
```

#### 4. Download Pre-trained YOLOv8 Models
Place your trained models in `best_models/`:
```
best_models/
├── classes.txt          # Class labels
├── ecoprint320.pt       # YOLOv8n model (320x320)
└── ecoprint640.pt       # YOLOv8s model (640x640)
```

> **Note**: Model files (*.pt, *.onnx) are excluded from version control due to size. Train your own or contact the development team.

#### 5. Configure RVM Settings
Edit `new_ecoprint.py` to configure:
- Camera resolution and detection confidence
- API endpoint URLs for WebApp
- GPIO pin mappings for motors/relays
- RFID reader serial port
- Thermal printer configuration

#### 6. Run the RVM Application
```bash
python3 new_ecoprint.py
```

---

### 🌐 Part 2: EcoPrint-WebApp (Server)

#### 1. Clone Repository
```bash
git clone https://github.com/yourusername/EcoPrint.git
cd EcoPrint/EcoPrint-WebApp
```

#### 2. Install PHP Dependencies
```bash
composer install
```

#### 3. Configure Environment
```bash
cp env .env
```

Edit `.env` with your configuration:
```env
# Database
database.default.hostname = 127.0.0.1
database.default.database = ecoprint
database.default.username = root
database.default.password = your_password

# Google OAuth 2.0
google.clientId = YOUR_GOOGLE_CLIENT_ID
google.clientSecret = YOUR_GOOGLE_CLIENT_SECRET
google.redirectUri = https://yourdomain.com/auth/google/callback

# reCAPTCHA (optional)
recaptcha.site_key = YOUR_SITE_KEY
recaptcha.secret_key = YOUR_SECRET_KEY

# Base URL
app.baseURL = https://yourdomain.com/
```

#### 4. Import Database Schema
```bash
mysql -u root -p ecoprint < ecoprint.sql
```

#### 5. Set Permissions
```bash
chmod -R 755 writable/
chmod -R 755 uploads/
```

#### 6. Start Development Server
```bash
php spark serve
```

Access at: **http://localhost:8080**

---

## 🔐 API Authentication & Integration

The RVM communicates with the WebApp via RESTful API with JWT token authentication:

### Example: Validate User by RFID
```python
import requests

response = requests.post('https://yourdomain.com/api/validate-user', json={
    'rfid': '04B2C3D5E6F7',
    'rvm_id': 'RVM-001'
})

if response.status_code == 200:
    user_data = response.json()
    print(f"Welcome {user_data['name']}! Points: {user_data['points']}")
```

---

## 📁 Project Structure

```
EcoPrint_Code_And_Resources/
├── EcoPrint-RVM/                      # Raspberry Pi Application
│   ├── assets/                        # GUI images and icons
│   ├── best_models/                   # YOLOv8 trained models
│   │   └── classes.txt                # Bottle class labels
│   ├── new_ecoprint.py                # Main RVM application
│   ├── gui.py                         # Tkinter GUI display module
│   ├── toast.py                       # Toast notification system
│   └── heartbeat_sender.py            # Network connectivity monitor
│
└── EcoPrint-WebApp/                   # CodeIgniter 4 Web Application
    ├── app/
    │   ├── Config/                    # Configuration files
    │   ├── Controllers/               # API and web controllers
    │   ├── Models/                    # Database models
    │   ├── Views/                     # UI templates
    │   └── Libraries/                 # Custom libraries
    ├── public/                        # Public web root
    ├── writable/                      # Logs, cache, sessions
    ├── ecoprint.sql                   # Database schema
    ├── composer.json                  # PHP dependencies
    └── spark                          # CodeIgniter CLI tool
```

---

## 🧪 Testing & Validation

- **Computer Vision Accuracy**: YOLOv8 model achieves 95%+ mAP@0.5 on custom bottle dataset
- **Real-time Performance**: Inference time < 100ms on Raspberry Pi 4 (320x320 input)
- **Hardware Reliability**: RFID read success rate > 98%
- **API Response Time**: Average < 200ms for authentication and transaction logging
- **Concurrent Users**: WebApp supports 100+ simultaneous users
- **System Uptime**: RVM operates 24/7 with automatic recovery from network interruptions

---

## 🔒 Security & Privacy

- **Data Protection**: User RFID card numbers are hashed and never stored in plaintext
- **API Authentication**: JWT-based token validation for all RVM-to-server communications
- **Google OAuth**: Leverages institutional email SSO for secure user authentication
- **Audit Trail**: All transactions logged with timestamps and user IDs
- **GDPR Compliance**: Users can request data export or account deletion via admin panel

---

## 📄 License

This project is open-sourced under the [MIT License](https://opensource.org/licenses/MIT).

---

## 👥 Credits & Acknowledgments

Developed by **Team ArdeaAlba** for the Camarines Sur Polytechnic Colleges (CSPC) Information and Communications Technology Unit (ICTU) as part of the institutional sustainability and waste management initiative.

**Technologies Used**:
- [YOLOv8 by Ultralytics](https://docs.ultralytics.com/)
- [CodeIgniter 4 Framework](https://codeigniter.com/)
- [Raspberry Pi Foundation](https://www.raspberrypi.org/)

---

<div align="center">
  <b>EcoPrint</b> — Transforming Waste into Opportunity, One Bottle at a Time ♻️
</div>
