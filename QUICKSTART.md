# 🚀 Quick Start Guide

## Get Up and Running in 3 Steps

### Step 1: Start the Docker Container

```bash
docker-compose up -d --build
```

Wait for the container to build and start (~2-3 minutes first time).

### Step 2: Install Dependencies

```bash
docker exec -it biit-api-mock composer install
```

### Step 3: Test the API

```bash
chmod +x test-api.sh
./test-api.sh
```

Or test manually:

```bash
curl -X POST http://localhost:8090/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"test@example.com","password":"password123"}'
```

## ✅ Success!

Your API is now running at: **http://localhost:8090**

---

## 📋 What's Next?

### Option 1: Use Postman
Import `postman_collection.json` into Postman for a complete testing interface.

### Option 2: Use cURL
Check the `README.md` file for detailed cURL examples.

### Option 3: Integrate with KonexCommerce
Point your KonexCommerce integration to `http://localhost:8090`

---

## 🔑 Default Credentials

**Integration Token:**
```
konex-integration-token-12345
```

**User Login:**
- Any email/password combination works for testing
- Example: `test@example.com` / `password123`

---

## 🐳 Useful Docker Commands

**View logs:**
```bash
docker-compose logs -f
```

**Restart:**
```bash
docker-compose restart
```

**Stop:**
```bash
docker-compose down
```

**Rebuild:**
```bash
docker-compose down
docker-compose up -d --build
```

---

## 📚 Documentation

- Full API documentation: `README.md`
- Integration specification: Check the document provided
- Postman collection: `postman_collection.json`

---

## ⚠️ Troubleshooting

**Port 8090 already in use?**
Edit `docker-compose.yml` and change the port mapping:
```yaml
ports:
  - "8091:80"  # Change to any available port
```

Then also update `.env`:
```
app.baseURL = 'http://localhost:8091/'
```

**Container won't start?**
```bash
docker-compose down -v
docker-compose up -d --build
```

---

## 🎯 Quick API Test Checklist

- [ ] Login and get token: `POST /auth/login`
- [ ] Get user prices: `GET /me/prices`
- [ ] Validate cart item: `POST /cart/item/validate`
- [ ] Get shipping options: `GET /cart/shipping-options`
- [ ] Export catalog: `GET /catalog/export`

All endpoints are documented in `README.md`!
