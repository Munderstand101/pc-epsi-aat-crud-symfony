pipeline {
    agent any

   
   environment {
        PATH = "C:\\Tools\\php\\php-8.3.8-nts-Win32-vs16-x64;$PATH"
    }

    
    stages {
        stage('Checkout') {
            steps {
                git url: 'https://github.com/Munderstand101/pc-epsi-aat-crud-symfony.git', branch: 'master'
            }
        }

stage('Prepare Database') {
            steps {
                // Migrate the database schema to the latest version
                bat 'php bin/console doctrine:migrations:migrate --env=test --no-interaction'
                // Load fixtures
                bat 'php bin/console doctrine:fixtures:load --env=test --no-interaction'
            }
        }
        
     stage('Install dependencies') {
            steps {
                // Utilisez la commande "bat" pour Windows
                bat 'composer install'
            }
        }

        stage('Run tests') {
            steps {
                // Utilisez la commande "bat" pour Windows
                bat 'php bin/phpunit --log-junit tests/report.xml'
            }
        }
    }

    post {
        always {
            junit 'tests/report.xml'
        }
    }
}
