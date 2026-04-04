# MikroTik Router Configuration Guide

## Overview

This guide provides step-by-step instructions for configuring MikroTik routers to work with the QalcuityERP Telecom Module.

---

## 📋 Prerequisites

- MikroTik Router with RouterOS v6.x or v7.x
- WinBox application or SSH access
- Network connectivity between ERP server and router
- Administrative credentials for router

---

## 🔧 Step 1: Enable API Service

### Via WinBox

1. Open **WinBox** and connect to your router
2. Go to **IP → Services**
3. Find **api** in the list
4. Double-click to edit
5. Set **Port**: `8728` (default)
6. Check **Enabled** checkbox
7. Click **OK**

### Via Terminal

```bash
/ip service enable api
/ip service set api port=8728
```

### Verify API is Running

```bash
/ip service print where name="api"
```

Expected output:
```
# NAME   PORT ADDRESS                         CERTIFICATE
0 api    8728 0.0.0.0/0
```

---

## 👤 Step 2: Create API User

### Security Best Practices

- Create dedicated user for API access
- Use strong password (min 12 characters)
- Assign minimal required permissions
- Disable default admin account if possible

### Create User via WinBox

1. Go to **System → Users**
2. Click **+** (Add New)
3. Fill in details:
   - **Name**: `erp_api_user`
   - **Password**: `YourSecurePassword123!`
   - **Group**: Create new group `api_group`
4. Click **OK**

### Create User Group with Permissions

1. Go to **System → User Groups**
2. Click **+** (Add New)
3. Name: `api_group`
4. Set permissions:
   - ✅ **Read**: hotspot, ip, interface, system
   - ✅ **Write**: hotspot, ip
   - ✅ **Test**: hotspot
   - ❌ **Policy**: All unchecked (no policy changes)
   - ❌ **Password**: Unchecked
5. Click **OK**

### Via Terminal

```bash
# Create user group
/user group add name=api_group \
  policy=read,write,test \
  local=yes telnet=yes ssh=yes ftp=yes api=yes winbox=yes

# Create API user
/user add name=erp_api_user \
  password=YourSecurePassword123! \
  group=api_group \
  comment="ERP System API Access"
```

---

## 🌐 Step 3: Configure Hotspot

### Create Hotspot Profile

1. Go to **IP → Hotspot → Server Profiles**
2. Click **+** (Add New)
3. Configure:
   - **Name**: `premium_profile`
   - **Address Pool**: `hotspot-pool`
   - **HTML Directory**: `hotspot` (default)
   - **Login By**: HTTP PAP, HTTP CHAP, HTTPS
4. Click **OK**

### Create IP Pool for Hotspot

```bash
/ip pool add name=hotspot-pool ranges=10.5.50.1-10.5.50.254
```

### Configure Hotspot Server

1. Go to **IP → Hotspot**
2. Click **Hotspot Setup**
3. Follow wizard:
   - **Hotspot Interface**: Select LAN interface (e.g., ether2)
   - **Local Address of Network**: `10.5.50.1/24`
   - **Address Pool**: `hotspot-pool`
   - **Select Certificate**: none
   - **DNS Servers**: `8.8.8.8,8.8.4.4`
   - **DNS Name**: `login.local`
4. Complete wizard

---

## 📊 Step 4: Configure Bandwidth Management

### Create Queue Tree for Monitoring

```bash
# Parent queue for all traffic
/queue tree add name="all-traffic" parent=global packet-mark="" \
  priority=8 max-limit=0 limit-at=0 burst-limit=0 burst-threshold=0 \
  burst-time=0s queue=default

# Download tracking
/queue tree add name="download-tracking" parent=all-traffic \
  packet-mark="" priority=8 max-limit=0 limit-at=0 queue=default \
  comment="Track download bandwidth"

# Upload tracking
/queue tree add name="upload-tracking" parent=all-traffic \
  packet-mark="" priority=8 max-limit=0 limit-at=0 queue=default \
  comment="Track upload bandwidth"
```

### Create Simple Queues for Rate Limiting

```bash
# Example: 50 Mbps package
/queue simple add name="package_50mbps" target=10.5.50.0/24 \
  max-limit=50M/20M \
  comment="Premium 50Mbps Package"

# Example: 10 Mbps package
/queue simple add name="package_10mbps" target="" \
  max-limit=10M/5M \
  comment="Basic 10Mbps Package"
```

---

## 🔐 Step 5: Firewall Configuration

### Allow API Access from ERP Server Only

```bash
# Replace 192.168.1.100 with your ERP server IP
/ip firewall filter add chain=input protocol=tcp dst-port=8728 \
  src-address=192.168.1.100 action=accept \
  comment="Allow API from ERP Server"

# Block API from all other sources
/ip firewall filter add chain=input protocol=tcp dst-port=8728 \
  action=drop comment="Block API from unauthorized sources"
```

### Allow Hotspot Traffic

```bash
/ip firewall nat add chain=srcnat out-interface=WAN \
  action=masquerade comment="Hotspot NAT"
```

---

## 📡 Step 6: Configure DHCP Server

### Setup DHCP for Hotspot Clients

```bash
/ip dhcp-server add name=dhcp-hotspot interface=ether2 \
  address-pool=hotspot-pool disabled=no lease-time=1h

/ip dhcp-server network add address=10.5.50.0/24 \
  gateway=10.5.50.1 dns-server=8.8.8.8,8.8.4.4 \
  domain=login.local
```

---

## 🎫 Step 7: Test Hotspot User Creation

### Create Test User via Terminal

```bash
/ip hotspot user add name=testuser001 password=test123 \
  profile=premium_profile comment="Test user from ERP"
```

### Verify User Created

```bash
/ip hotspot user print where name="testuser001"
```

Expected output:
```
Flags: X - disabled, R - radius
 #   NAME            PROFILE         LIMIT-UPTIME LIMIT-BYTES-IN LIMIT-BYTES-OUT
 0   testuser001     premium_profile
```

### Remove Test User

```bash
/ip hotspot user remove [find name="testuser001"]
```

---

## 🔗 Step 8: Connect to ERP System

### Register Device in ERP

**API Call:**
```bash
curl -X POST https://your-erp.com/api/telecom/devices \
  -H "Authorization: Bearer YOUR_API_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Main Office Router",
    "brand": "mikrotik",
    "device_type": "router",
    "ip_address": "192.168.88.1",
    "port": 8728,
    "username": "erp_api_user",
    "password": "YourSecurePassword123!",
    "location": "Server Room",
    "notes": "Primary internet gateway"
  }'
```

### Test Connection from ERP

Navigate to: **Telecom → Devices → [Your Device] → Test Connection**

Expected result: ✅ Connection successful

---

## 📈 Step 9: Configure Logging

### Enable Detailed Logging for Troubleshooting

```bash
/system logging add topics=hotspot,api message="Hotspot and API events"
```

### View Logs

```bash
/log print follow
```

### Export Logs to Remote Syslog (Optional)

```bash
/system logging action add name=remote-syslog remote=192.168.1.100 \
  remote-port=514 syslog-facility=local0 target=remote

/system logging add topics=hotspot action=remote-syslog
```

---

## 🛡️ Step 10: Security Hardening

### Disable Unused Services

```bash
/ip service disable telnet,ftp,www,api-ssl
```

### Enable SSH with Key Authentication

```bash
# Generate SSH key on ERP server
ssh-keygen -t rsa -b 4096

# Copy public key to router
/user ssh-keys import public-key-file=erp_server.pub user=erp_api_user

# Disable password authentication
/ip service set ssh address=192.168.1.100/32
```

### Configure Backup Schedule

```bash
/system scheduler add name="daily-backup" interval=1d \
  on-event="/system backup save name=backup-\[/system clock get date\]"
```

---

## 🧪 Step 11: Testing Checklist

- [ ] API service enabled and accessible
- [ ] API user created with correct permissions
- [ ] Hotspot server configured and running
- [ ] DHCP server assigning IP addresses
- [ ] Firewall rules blocking unauthorized API access
- [ ] Can create hotspot user via terminal
- [ ] Can remove hotspot user via terminal
- [ ] ERP system can connect to router
- [ ] Bandwidth monitoring working
- [ ] Logs capturing relevant events

---

## 🔍 Troubleshooting

### Issue: Cannot Connect via API

**Check:**
```bash
# Verify API service is running
/ip service print where name="api"

# Check firewall rules
/ip firewall filter print where dst-port=8728

# Test connection from ERP server
telnet 192.168.88.1 8728
```

**Solution:**
- Ensure API service is enabled
- Check firewall allows connection from ERP IP
- Verify username/password are correct

---

### Issue: Hotspot Users Not Created

**Check:**
```bash
# Verify hotspot server is running
/ip hotspot print

# Check hotspot profile exists
/ip hotspot profile print

# Review logs
/log print where message~"hotspot"
```

**Solution:**
- Ensure hotspot server is enabled
- Verify profile name matches API request
- Check user permissions include hotspot write access

---

### Issue: Bandwidth Data Not Updating

**Check:**
```bash
# Verify queue tree exists
/queue tree print

# Check active connections
/ip hotspot active print

# Monitor traffic
/interface monitor-traffic ether2
```

**Solution:**
- Ensure queue tree is configured
- Verify users are assigned to correct profiles
- Check ERP polling job is running

---

## 📚 Additional Resources

- [MikroTik RouterOS Manual](https://wiki.mikrotik.com/wiki/Manual:TOC)
- [Hotspot Setup Guide](https://wiki.mikrotik.com/wiki/Manual:IP/Hotspot)
- [API Documentation](https://wiki.mikrotik.com/wiki/Manual:API)
- [Queue Tree Examples](https://wiki.mikrotik.com/wiki/Manual:Queue#Queue_Tree)

---

## 📞 Support

For technical support:
- Email: support@qalcuity.com
- Documentation: https://docs.qalcuity.com/telecom
- Community Forum: https://community.qalcuity.com

---

**Last Updated:** April 4, 2026  
**RouterOS Version:** 6.x / 7.x  
**Guide Version:** 1.0.0
