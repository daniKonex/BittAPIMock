# Biit API Mock - KonexCommerce Integration

Mock API for testing the integration between KonexCommerce and Biit/Sage ERP system. Built with CodeIgniter 4 and PHP 8.2.

🌐 **Live Production**: Deployed on Railway with automatic CI/CD

## 🚀 Quick Start

### Prerequisites
- Docker & Docker Compose
- Git

### Installation

1. **Clone and navigate to the project:**
```bash
cd /Users/danikonex/Code/BittAPIMock
```

2. **Build and start the Docker container:**
```bash
docker-compose up -d --build
```

3. **Install dependencies:**
```bash
docker exec -it biit-api-mock composer install
```

4. **Access the API:**
The API will be available at: `http://localhost:8090`

**Note:** Port 8090 is used to avoid conflicts with other projects.

---

## 📋 API Endpoints

### Authentication

#### **POST /auth/login**
Authenticates a user with credentials and returns a User Access Token.

**Headers:**
```
Content-Type: application/json
```

**Request Body:**
```json
{
  "email": "user@example.com",
  "password": "password123"
}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
    "tokenType": "Bearer",
    "expiresIn": 3600,
    "user": {
      "id": "USR1234",
      "email": "user@example.com",
      "name": "Test User",
      "clientId": "CLI1234",
      "roles": ["customer"],
      "allowedClients": [...]
    }
  }
}
```

#### **POST /auth/guest-register**
Registers a guest user.

**Request Body:**
```json
{
  "email": "guest@example.com",
  "name": "Guest User",
  "phone": "+34600000000"
}
```

---

### Token Management

#### **POST /token/force-login**
Generates a User Access token for another user (for sales reps, admins).

**Headers:**
```
Authorization: Bearer konex-integration-token-12345
Content-Type: application/json
```

**Request Body:**
```json
{
  "userId": "USR1234",
  "clientId": "CLI1234"
}
```

#### **POST /token/refresh**
Refreshes an expired User Access Token.

**Headers:**
```
Authorization: Bearer konex-integration-token-12345
Content-Type: application/json
```

**Request Body:**
```json
{
  "token": "expired-jwt-token"
}
```

---

### User/Client Information

All endpoints require User Access Token in the Authorization header:
```
Authorization: Bearer <user-access-token>
```

#### **GET /me/prices**
Returns personalized prices for the authenticated client.

**Response:**
```json
{
  "success": true,
  "data": {
    "clientId": "CLI1234",
    "priceList": "TARIFF_01",
    "currency": "EUR",
    "prices": [...]
  }
}
```

#### **GET /me/catalog**
Returns filtered catalog based on client permissions.

#### **GET /me/addresses**
Returns shipping and billing addresses for the client.

#### **GET /me/orders**
Returns order history for the client.

---

### Cart Management (Model 2 - Recommended)

#### **POST /cart/item/validate**
Validates a product before adding to cart.

**Headers:**
```
Authorization: Bearer <user-or-guest-token>
Content-Type: application/json
```

**Request Body:**
```json
{
  "sku": "IPAD-PRO-128-GRAY",
  "qty": 1
}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "sku": "IPAD-PRO-128-GRAY",
    "qty": 1,
    "price": 854.99,
    "available": true,
    "stockAvailable": 50
  }
}
```

#### **POST /cart/validate**
Validates the entire cart before checkout.

**Request Body:**
```json
{
  "items": [
    {"sku": "IPAD-PRO-128-GRAY", "qty": 1}
  ],
  "shippingAddressId": "ADDR001",
  "shippingMethodId": "STANDARD"
}
```

#### **GET /cart/shipping-options**
Returns available shipping methods.

**Query Parameters:**
- `cartTotal` (optional): Total cart amount
- `shippingAddressId` (optional): Shipping address ID

#### **POST /cart/confirm**
Confirms and creates the order after payment.

**Request Body:**
```json
{
  "items": [...],
  "shippingAddressId": "ADDR001",
  "billingAddressId": "ADDR002",
  "shippingMethodId": "STANDARD",
  "paymentMethod": "credit_card",
  "paymentReference": "PAY123456"
}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "orderId": "ORD202401011234",
    "orderNumber": "ORD202401011234",
    "status": "confirmed",
    "createdAt": "2024-01-01T10:30:00+00:00",
    "total": 899.99,
    "currency": "EUR",
    "message": "Order confirmed successfully"
  }
}
```

---

### Catalog Synchronization

#### **GET /catalog/export**
Exports the full catalog in JSON format.

**Headers:**
```
Authorization: Bearer konex-integration-token-12345
X-Full-Sync: true
```

**Response:** Returns a comprehensive JSON with:
- Metadata
- Collections
- Configurable Metadatas
- Products
- Configurable Products

---

## 🔐 Authentication

### Integration Token
Used by KonexCommerce to authenticate as a platform:
```
Authorization: Bearer konex-integration-token-12345
```

### User Access Token
JWT token for authenticated users, obtained via `/auth/login`:
```
Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGc...
```

### Guest Token
JWT token for guest users, obtained via `/auth/guest-register`.

---

## 🛠️ Configuration

### Environment Variables

Edit the `.env` file to customize:

```env
# Application
CI_ENVIRONMENT = development
app.baseURL = 'http://localhost:8090/'

# Security
JWT_SECRET_KEY = 'your-secret-key-change-in-production-2024'
JWT_TIME_TO_LIVE = 3600

# Integration
INTEGRATION_TOKEN = 'konex-integration-token-12345'
```

### Docker Ports

To change the port, edit `docker-compose.yml`:
```yaml
ports:
  - "8090:80"  # Change 8090 to your desired port
```

---

## 📝 Testing Examples

### Using cURL

**Login:**
```bash
curl -X POST http://localhost:8090/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"test@example.com","password":"password"}'
```

**Get User Prices:**
```bash
curl -X GET http://localhost:8090/me/prices \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

**Export Catalog:**
```bash
curl -X GET http://localhost:8090/catalog/export \
  -H "Authorization: Bearer konex-integration-token-12345" \
  -H "X-Full-Sync: true"
```

**Validate Cart Item:**
```bash
curl -X POST http://localhost:8090/cart/item/validate \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Content-Type: application/json" \
  -d '{"sku":"IPAD-PRO-128-GRAY","qty":1}'
```

---

## 🐳 Docker Commands

**Start containers:**
```bash
docker-compose up -d
```

**Stop containers:**
```bash
docker-compose down
```

**View logs:**
```bash
docker-compose logs -f
```

**Restart containers:**
```bash
docker-compose restart
```

**Access container shell:**
```bash
docker exec -it biit-api-mock bash
```

---

## 📂 Project Structure

```
BittAPIMock/
├── app/
│   ├── Config/
│   │   ├── App.php           # Application config
│   │   ├── Filters.php       # Filter configuration
│   │   ├── Paths.php         # Path definitions
│   │   └── Routes.php        # API routes
│   ├── Controllers/
│   │   ├── Auth.php          # Authentication endpoints
│   │   ├── Token.php         # Token management
│   │   ├── User.php          # User/client endpoints
│   │   ├── Cart.php          # Cart operations (Model 2)
│   │   └── Catalog.php       # Catalog export
│   └── Filters/
│       └── AuthFilter.php    # JWT authentication
├── docker/
│   └── apache/
│       └── 000-default.conf  # Apache configuration
├── public/
│   ├── .htaccess            # Apache rewrite rules
│   └── index.php            # Entry point
├── writable/                # Logs and cache
├── .env                     # Environment config
├── docker-compose.yml       # Docker orchestration
├── Dockerfile               # Docker image
└── composer.json            # PHP dependencies
```

---

## 🔧 Development

### Add New Endpoints

1. Create/modify controller in `app/Controllers/`
2. Add route in `app/Config/Routes.php`
3. Apply authentication filter if needed

### Modify Mock Data

Edit the respective controller methods to return different mock responses.

---

## ⚠️ Important Notes

- This is a **mock API for testing only** - no real validation or persistence
- Mock data is hardcoded for demonstration purposes
- JWT secret should be changed in production
- All endpoints accept any valid format data for testing flexibility
- Port 8090 is used to avoid conflicts with other projects

---

## 📚 Integration Documentation

This mock implements the integration specification between KonexCommerce and Biit/Sage as defined in the integration document, specifically:

- **Model 2 (Recommended)** for cart management
- JWT-based authentication with Integration and User tokens
- Multi-language support in catalog responses
- Catalog synchronization via JSON export

---

## 🆘 Troubleshooting

**Container won't start:**
```bash
docker-compose down -v
docker-compose up -d --build
```

**Permission issues:**
```bash
docker exec -it biit-api-mock chown -R www-data:www-data /var/www/html/writable
```

**Port already in use:**
Change the port in `docker-compose.yml` and `.env`

---

## 📄 License

This is a mock API for testing purposes. Use at your own discretion.
