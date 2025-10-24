#!/bin/bash

# Biit API Mock - Setup Verification Script

echo "=========================================="
echo "Biit API Mock - Setup Verification"
echo "=========================================="
echo ""

# Check if Docker is running
echo "1. Checking Docker..."
if docker info > /dev/null 2>&1; then
    echo "   ✅ Docker is running"
else
    echo "   ❌ Docker is not running. Please start Docker Desktop."
    exit 1
fi

echo ""

# Check if port 8090 is available
echo "2. Checking port 8090..."
if lsof -Pi :8090 -sTCP:LISTEN -t >/dev/null 2>&1; then
    echo "   ⚠️  Port 8090 is in use"
    echo "   You may need to change the port in docker-compose.yml"
else
    echo "   ✅ Port 8090 is available"
fi

echo ""

# Check if container is running
echo "3. Checking container status..."
if docker ps | grep -q "biit-api-mock"; then
    echo "   ✅ Container is running"
    CONTAINER_RUNNING=true
else
    echo "   ⚠️  Container is not running"
    echo "   Run: docker-compose up -d --build"
    CONTAINER_RUNNING=false
fi

echo ""

# Test API if container is running
if [ "$CONTAINER_RUNNING" = true ]; then
    echo "4. Testing API endpoint..."
    RESPONSE=$(curl -s -o /dev/null -w "%{http_code}" http://localhost:8090/)
    
    if [ "$RESPONSE" = "200" ]; then
        echo "   ✅ API is responding (HTTP $RESPONSE)"
        echo ""
        echo "=========================================="
        echo "✅ Setup verification complete!"
        echo "=========================================="
        echo ""
        echo "Your API is ready at: http://localhost:8090"
        echo ""
        echo "Next steps:"
        echo "  - Import postman_collection.json into Postman"
        echo "  - Run ./test-api.sh for a quick test"
        echo "  - Check README.md for full documentation"
    else
        echo "   ⚠️  API returned HTTP $RESPONSE"
        echo "   Check logs: docker-compose logs -f"
    fi
else
    echo "4. Skipping API test (container not running)"
    echo ""
    echo "=========================================="
    echo "⚠️  Setup incomplete"
    echo "=========================================="
    echo ""
    echo "To complete setup:"
    echo "  1. docker-compose up -d --build"
    echo "  2. docker exec -it biit-api-mock composer install"
    echo "  3. ./verify-setup.sh (run this again)"
fi

echo ""
