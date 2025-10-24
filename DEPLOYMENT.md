# Railway Deployment Setup

This project is configured to deploy automatically to Railway using GitHub Actions.

## Setup Instructions

### 1. Railway Project Setup
1. Create a new project on Railway
2. Connect your GitHub repository
3. Note down your Railway Service ID

### 2. GitHub Secrets Configuration
Add the following secrets to your GitHub repository settings:

- `RAILWAY_TOKEN`: Your Railway production token
- `RAILWAY_TOKEN_STAGING`: Your Railway staging token (if using staging)
- `RAILWAY_SERVICE_ID`: Your Railway service ID

### 3. Branch Configuration
- Push to `staging` branch to deploy to staging environment
- Push to `production` branch to deploy to production environment

### 4. Manual Deployment
You can also trigger deployments manually using the "Actions" tab in GitHub.

## Railway Configuration

The project uses the following Railway configuration (`railway.toml`):
- **Builder**: Dockerfile
- **Health Check**: `/` endpoint
- **Environment**: Production settings
- **Restart Policy**: On failure

## Environment Variables

Railway will automatically provide:
- `PORT`: The port your application should listen on
- `CI_ENVIRONMENT`: Set to "production"

Add any additional environment variables through the Railway dashboard.
