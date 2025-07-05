# Field Recording Manager - Docker Setup

This guide explains how to run the Field Recording Manager using Docker.

## Prerequisites

- Docker
- Docker Compose

## Quick Start

1. **Clone the repository** (if not already done):
   ```bash
   git clone <repository-url>
   cd fieldrecordingmanager
   ```

2. **Build and start the container**:
   ```bash
   docker-compose up -d
   ```

3. **Access the application**:
   Open your browser and go to: `http://localhost:8080`

## Docker Configuration

### Ports
- **8080**: Web interface (HTTP)

### Volumes
The following directories are persisted:
- `field_recordings_data`: Database and application data
- `field_recordings_uploads`: Audio file uploads
- `field_recordings_logs`: Application logs

### Environment
- **PHP 8.2** with Apache
- **SQLite** database
- **Apache modules**: rewrite, headers

## Management Commands

### Start the application
```bash
docker-compose up -d
```

### Stop the application
```bash
docker-compose down
```

### View logs
```bash
docker-compose logs -f field_recordings
```

### Rebuild after changes
```bash
docker-compose down
docker-compose build --no-cache
docker-compose up -d
```

### Access container shell
```bash
docker exec -it field_recordings_manager bash
```

## Data Backup

To backup your data:
```bash
# Backup volumes
docker run --rm -v field_recordings_data:/data -v $(pwd):/backup alpine tar czf /backup/data-backup.tar.gz -C /data .
docker run --rm -v field_recordings_uploads:/data -v $(pwd):/backup alpine tar czf /backup/uploads-backup.tar.gz -C /data .
```

To restore:
```bash
# Restore volumes
docker run --rm -v field_recordings_data:/data -v $(pwd):/backup alpine tar xzf /backup/data-backup.tar.gz -C /data
docker run --rm -v field_recordings_uploads:/data -v $(pwd):/backup alpine tar xzf /backup/uploads-backup.tar.gz -C /data
```

## Troubleshooting

### Check container status
```bash
docker-compose ps
```

### Check container health
```bash
docker inspect field_recordings_manager | grep Health -A 10
```

### Reset everything
```bash
docker-compose down -v
docker-compose up -d
```

## Security Notes

- The application runs as `www-data` user inside the container
- Upload and data directories have appropriate permissions
- Apache is configured with security headers
- SQLite database is stored in a persistent volume

## Performance

- The container includes health checks
- Automatic restart on failure
- Optimized file permissions
- Efficient Docker layer caching