pipeline {
    agent any

    stages {
        stage('Checkout') {
            steps {
                git url: 'https://github.com/Munderstand101/pc-epsi-aat-crud-symfony.git', branch: 'master'
            }
        }

        stage('Install dependencies') {
            steps {
                sh 'composer install'
            }
        }

        stage('Run tests') {
            steps {
                sh 'php bin/phpunit'
            }
        }
    }

    post {
        always {
            junit 'tests/report.xml'
        }
    }
}
