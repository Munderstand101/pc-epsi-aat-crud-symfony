pipeline {
    agent any

    environment {
        PATH = "$PATH:/usr/local/bin" // Ajoutez le chemin de Git si nécessaire
    }

    stages {
        stage('Checkout') {
            steps {
                git url: 'https://github.com/Munderstand101/pc-epsi-aat-crud-symfony.git', branch: 'master'
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
