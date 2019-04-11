# Indianautosblog.com website

## Running project:
1. Install Docker and Docker Compose
2. Go to project folder and run docker
    ```bash
    cd /path/to/project
    docker-compose up -d --build
    ```
3. Run composer install
    ```bash
    sudo docker-compose exec all_php composer install
    ```
4. Host local website
    - Docker for Windows: Add `127.0.0.1	hindi.indianautosblog.local` to `hosts` file.
    - Docker toolbox: Add `192.168.99.100	hindi.indianautosblog.local` to `hosts` file.
    
5. Copy docker-compose.yml.dist to docker-compose.yml in root of project

6. Mount code folder for docker:
    - Docker for Windows: Replace from `/projects/indianautosblog/hindi-web/` to `./` in docker-compose.yml file
    - Docker Toolbox: Mount driver in Docker Toolbox
        + Step 1: Add shared folder in Virtual Box, ex: PhpProjects -> D:\PhpProject (Root of all project)
        + Step 2: Open Docker Quickstart Terminal and Run command: `docker-machine ssh`
        + Step 3: Run command: `sudo vi /var/lib/boot2docker/bootlocal.sh`
        + Step 4: Paste content below and save
            - `sudo mkdir /projects`
            - `sudo mount -t vboxsf PhpProjects /projects`
        + Step 5: Quit machine by command: `exit`
        + Step 6: Restart machine with command: `docker-machine restart`
 
    Go to browser and type `http://hindi.indianautosblog.local/`

## Run code quality checker tools

### PHP Install composer
```bash
docker-compose exec all_php composer install
```

### PHP CodeSniffer 

Must run PHP CodeSniffer before creating pull request and fix all violations
```bash
sudo docker-compose exec all_php vendor/bin/phpcs --standard=phpcs.xml --extensions=php .
```

Some violations can auto fix with `phpcbf`
```bash
sudo docker-compose exec all_php vendor/bin/phpcbf --standard=phpcs.xml --extensions=php .
```

### PHP Mess Detector 

Must run PHP Mess Detector before creating pull request and fix all violations
```bash
sudo docker-compose exec app vendor/bin/phpmd . text ruleset.xml --suffixes php --exclude .idea,app,bin,web,docker,vendor,tests,var/cache/,var/logs/,var/sessions/,var/SymfonyRequirements.php
```