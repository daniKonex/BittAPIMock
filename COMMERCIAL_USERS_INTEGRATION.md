# Integración de Usuarios Comerciales

## Descripción General

Los usuarios comerciales (aquellos cuyo email contiene "comercial") pueden actuar en nombre de múltiples clientes. Cada cliente tiene su propio email, direcciones y datos asociados.

## Endpoint de Login

### POST /auth/login

**Comportamiento para usuarios comerciales:**

Cuando el email contiene "comercial", el endpoint devuelve 3 clientes en el array `allowedClients`:

```json
{
  "success": true,
  "data": {
    "token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
    "tokenType": "Bearer",
    "expiresIn": 3600,
    "user": {
      "id": "USR1234",
      "email": "comercial@konex.es",
      "name": "Usuario Comercial",
      "clientId": "CLI-ABCD01",
      "roles": ["customer"],
      "allowedClients": [
        {
          "clientId": "CLI-ABCD01",
          "email": "cliente1.comercial@konex.es",
          "name": "Cliente Principal S.L.",
          "canCreateOrders": true,
          "addresses": [
            {
              "id": "ADDR-ABCD01-01",
              "type": "shipping",
              "name": "Cliente Principal S.L.",
              "street": "Calle Principal 123",
              "city": "Madrid",
              "postalCode": "28001",
              "province": "Madrid",
              "country": "ES",
              "phone": "+34 910 000 001",
              "isDefault": true
            },
            {
              "id": "ADDR-ABCD01-02",
              "type": "shipping",
              "name": "Cliente Principal S.L. - Almacén",
              "street": "Polígono Industrial 45",
              "city": "Alcalá de Henares",
              "postalCode": "28802",
              "province": "Madrid",
              "country": "ES",
              "phone": "+34 910 000 002",
              "isDefault": false
            }
          ]
        },
        {
          "clientId": "CLI-ABCD02",
          "email": "cliente2.comercial@konex.es",
          "name": "Cliente Secundario S.A.",
          "canCreateOrders": true,
          "addresses": [...]
        },
        {
          "clientId": "CLI-ABCD03",
          "email": "cliente3.comercial@konex.es",
          "name": "Cliente Terciario S.L.",
          "canCreateOrders": true,
          "addresses": [...]
        }
      ]
    }
  }
}
```

## Integración en el Frontend

### 1. Selector de Cliente

Cuando `allowedClients.length > 1`, mostrar un selector para que el usuario comercial elija con qué cliente desea actuar.

### 2. Header X-Acting-As-Client

**IMPORTANTE:** Una vez seleccionado un cliente, el frontend debe incluir el header `X-Acting-As-Client` en todas las peticiones a la API:

```javascript
headers: {
  'Authorization': 'Bearer ' + token,
  'X-Acting-As-Client': 'cliente2.comercial@konex.es'
}
```

### 3. Endpoint /me/addresses

Este endpoint ahora respeta el header `X-Acting-As-Client` y devuelve las direcciones del cliente seleccionado:

**Request:**
```http
GET /me/addresses
Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGc...
X-Acting-As-Client: cliente2.comercial@konex.es
```

**Response:**
```json
{
  "success": true,
  "data": {
    "clientId": "CLI-ABCD02",
    "clientEmail": "cliente2.comercial@konex.es",
    "clientName": "Cliente Secundario S.A.",
    "addresses": [
      {
        "id": "ADDR-ABCD02-01",
        "type": "shipping",
        "name": "Cliente Secundario S.A.",
        "street": "Avenida Secundaria 456",
        "city": "Barcelona",
        "postalCode": "08001",
        "province": "Barcelona",
        "country": "ES",
        "phone": "+34 930 000 001",
        "isDefault": true
      },
      {
        "id": "ADDR-ABCD02-02",
        "type": "shipping",
        "name": "Cliente Secundario S.A. - Sucursal",
        "street": "Calle Diagonal 789",
        "city": "Barcelona",
        "postalCode": "08019",
        "province": "Barcelona",
        "country": "ES",
        "phone": "+34 930 000 002",
        "isDefault": false
      }
    ]
  }
}
```

## Persistencia de Datos

Los clientes y sus direcciones se generan de forma **determinista** basándose en el hash del email comercial. Esto garantiza que:

- El mismo email comercial siempre obtiene los mismos 3 clientes
- Los IDs de cliente y direcciones son consistentes entre sesiones
- Los datos se persisten en `writable/cache/commercial_clients_mapping.json`

## Flujo de Checkout

### Problema Resuelto

**Antes:** El checkout mostraba "Acceso concedido como: comercial@konex.es"  
**Ahora:** El checkout debe mostrar "Acceso concedido como: cliente2.comercial@konex.es"

### Solución

1. El frontend debe leer el email del cliente seleccionado desde `allowedClients[selectedIndex].email`
2. Incluir el header `X-Acting-As-Client` con ese email en todas las peticiones
3. El endpoint `/me/addresses` devolverá automáticamente las direcciones correctas del cliente seleccionado

### Direcciones en el Checkout

Las direcciones devueltas por `/me/addresses` deben mostrarse como **direcciones conocidas** en la sección de envío del checkout, permitiendo al usuario:

- Seleccionar una dirección existente del cliente
- Ver todas las direcciones asociadas al cliente seleccionado
- Usar la dirección marcada como `isDefault: true` por defecto

## Ejemplo de Implementación Frontend

```javascript
// Después del login
const loginResponse = await login(email, password);
const allowedClients = loginResponse.data.user.allowedClients;

// Si hay múltiples clientes, mostrar selector
if (allowedClients.length > 1) {
  const selectedClient = await showClientSelector(allowedClients);
  
  // Guardar el email del cliente seleccionado
  localStorage.setItem('actingAsClient', selectedClient.email);
}

// En todas las peticiones subsiguientes
const actingAsClient = localStorage.getItem('actingAsClient');
const headers = {
  'Authorization': `Bearer ${token}`,
  ...(actingAsClient && { 'X-Acting-As-Client': actingAsClient })
};

// Obtener direcciones del cliente seleccionado
const addressesResponse = await fetch('/me/addresses', { headers });
// Las direcciones serán específicas del cliente seleccionado
```

## Notas Técnicas

- Los clientes se generan con IDs únicos basados en el hash del email comercial
- Cada cliente tiene 2 direcciones de envío por defecto
- Los datos son consistentes: el mismo email comercial siempre genera los mismos clientes
- El sistema soporta tanto usuarios comerciales como usuarios regulares sin cambios en el frontend para usuarios regulares
