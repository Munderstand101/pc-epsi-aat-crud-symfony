<?php

namespace App\Test\Controller;

use App\Entity\Task;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class TaskControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $manager;
    private EntityRepository $repository;
    private string $path = '/task/';

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->manager = static::getContainer()->get('doctrine')->getManager();
        $this->repository = $this->manager->getRepository(Task::class);

        foreach ($this->repository->findAll() as $object) {
            $this->manager->remove($object);
        }

        $this->manager->flush();
    }

    public function testIndex(): void
    {
        // Create some tasks to test with
        $task1 = new Task();
        $task1->setTitle('Test Task 1');
        $task1->setDescription('This is the first test task.');
        $this->manager->persist($task1);

        $task2 = new Task();
        $task2->setTitle('Test Task 2');
        $task2->setDescription('This is the second test task.');
        $this->manager->persist($task2);

        $this->manager->flush();

        // Request the index page
        $crawler = $this->client->request('GET', $this->path);

        // Check the response status code
        self::assertResponseStatusCodeSame(200);

        // Check the page title
        self::assertPageTitleContains('Task index');

        // Check for presence of a header element
        self::assertSelectorTextContains('h1', 'Task index');

        // Check that the table contains the correct number of rows
        self::assertCount(2, $crawler->filter('tbody tr'));

        // Check that the tasks are displayed correctly
        $taskRows = $crawler->filter('tbody tr');

        $this->assertCount(2, $taskRows);

        $firstRow = $taskRows->eq(0);
        $secondRow = $taskRows->eq(1);

        // Adjust the index to match the correct column
        $firstTitle = $firstRow->filter('td')->eq(1)->text();
        $firstDescription = $firstRow->filter('td')->eq(2)->text();
        $secondTitle = $secondRow->filter('td')->eq(1)->text();
        $secondDescription = $secondRow->filter('td')->eq(2)->text();

        self::assertSame('Test Task 1', $firstTitle);
        self::assertSame('This is the first test task.', $firstDescription);
        self::assertSame('Test Task 2', $secondTitle);
        self::assertSame('This is the second test task.', $secondDescription);
    }

    public function testNew(): void
    {
        // Request the new task creation page
        $crawler = $this->client->request('GET', sprintf('%snew', $this->path));

        // Check the response status code
        self::assertResponseStatusCodeSame(200);

        // Check the presence of the form
        self::assertSelectorExists('form');

        // Submit the form with new task data
        $this->client->submitForm('Save', [
            'task[title]' => 'Testing',
            'task[description]' => 'Testing description',
        ]);

        // Check for a redirect after form submission
        self::assertResponseRedirects($this->path);

        // Follow the redirect
        $this->client->followRedirect();

        // Check that the task has been added to the database
        self::assertSame(1, $this->repository->count([]));

        // Verify the task in the database
        $task = $this->repository->findOneBy(['title' => 'Testing']);
        self::assertNotNull($task);
        self::assertSame('Testing', $task->getTitle());
        self::assertSame('Testing description', $task->getDescription());
    }

    public function testShow(): void
    {
        // Create a task to test with
        $fixture = new Task();
        $fixture->setTitle('My Title');
        $fixture->setDescription('My Description');

        $this->manager->persist($fixture);
        $this->manager->flush();

        // Request the show page for the task
        $crawler = $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));

        // Check the response status code
        self::assertResponseStatusCodeSame(200);

        // Check the page title
        self::assertPageTitleContains('Task');

        // Check that the task's title is displayed
        $title = $crawler->filterXPath('//tr[th[text()="Title"]]/td')->text();
        self::assertSame('My Title', $title);

        // Check that the task's description is displayed
        $description = $crawler->filterXPath('//tr[th[text()="Description"]]/td')->text();
        self::assertSame('My Description', $description);
    }

    public function testEdit(): void
    {
        // Create a task to test with
        $fixture = new Task();
        $fixture->setTitle('Value');
        $fixture->setDescription('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        // Request the edit page for the task
        $crawler = $this->client->request('GET', sprintf('%s%s/edit', $this->path, $fixture->getId()));

        // Check the response status code
        self::assertResponseStatusCodeSame(200);

        // Submit the form with new task data
        $this->client->submitForm('Update', [
            'task[title]' => 'Something New',
            'task[description]' => 'Something New',
        ]);

        // Check for a redirect after form submission
        self::assertResponseRedirects('/task/');

        // Follow the redirect
        $this->client->followRedirect();

        // Verify the task in the database
        $updatedTask = $this->repository->find($fixture->getId());
        self::assertNotNull($updatedTask);
        self::assertSame('Something New', $updatedTask->getTitle());
        self::assertSame('Something New', $updatedTask->getDescription());
    }

    public function testRemove(): void
    {
        // Create a task to test with
        $fixture = new Task();
        $fixture->setTitle('Delete Task');
        $fixture->setDescription('This is a task to delete.');

        $this->manager->persist($fixture);
        $this->manager->flush();

        // Request the show page for the task
        $crawler = $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));

        // Submit the form to delete the task
        $this->client->submitForm('Delete');

        // Check for a redirect after form submission
        self::assertResponseRedirects($this->path);

        // Follow the redirect
        $this->client->followRedirect();

        // Verify the task has been removed from the database
        self::assertSame(0, $this->repository->count(['id' => $fixture->getId()]));
    }
}
