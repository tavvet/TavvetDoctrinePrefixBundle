<?php

namespace Tavvet\DoctrinePrefixBundle\Tests\Functional;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpKernel\KernelInterface;
use Tavvet\DoctrinePrefixBundle\Tests\Functional\Fixtures\Entity\Article;
use Tavvet\DoctrinePrefixBundle\Tests\Functional\Fixtures\Entity\Category;
use Tavvet\DoctrinePrefixBundle\Tests\Functional\Fixtures\Entity\Tag;

/**
 * End-to-end proof that the bundle produces a correct, working schema in a real
 * Symfony + DoctrineBundle app - not just that PrefixNamingStrategy delegates.
 * This is the test that would have caught the container-resolution bug at CI time.
 */
class SchemaGenerationTest extends KernelTestCase
{
    /** @var callable|null */
    private $baselineExceptionHandler;

    protected static function createKernel(array $options = []): KernelInterface
    {
        return new TestKernel('test', true);
    }

    protected function setUp(): void
    {
        parent::setUp();

        // Remember the exception handler PHPUnit expects to be in place.
        $this->baselineExceptionHandler = set_exception_handler(null);
        restore_exception_handler();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        // Symfony's ErrorHandler installs an exception handler when the kernel
        // boots and never restores it, which PHPUnit flags as risky. Pop every
        // handler stacked on top of the baseline (works whether one or several
        // leaked, and never drains PHPUnit's own handler).
        while (true) {
            $current = set_exception_handler(null);
            restore_exception_handler();

            if ($current === $this->baselineExceptionHandler || null === $current) {
                break;
            }

            restore_exception_handler();
        }
    }

    private function entityManager(): EntityManagerInterface
    {
        self::bootKernel();

        return self::getContainer()->get(EntityManagerInterface::class);
    }

    public function testGeneratedSqlHasPrefixedNamesAndConsistentForeignKeys(): void
    {
        $em = $this->entityManager();
        $metadata = $em->getMetadataFactory()->getAllMetadata();

        $sql = implode("\n", (new SchemaTool($em))->getCreateSchemaSql($metadata));

        // Table names carry the table prefix.
        self::assertStringContainsString('CREATE TABLE t_category', $sql);
        self::assertStringContainsString('CREATE TABLE t_article', $sql);
        // Join table: prefixed once, not twice.
        self::assertStringContainsString('CREATE TABLE t_article_tag', $sql);
        self::assertStringNotContainsString('t_t_', $sql);

        // Column names carry the column prefix.
        self::assertStringContainsString('c_name', $sql);
        self::assertStringContainsString('c_title', $sql);

        // The ManyToOne join column and the referenced primary key are both
        // prefixed and consistent with each other.
        self::assertStringContainsString('c_category_id', $sql);
        self::assertMatchesRegularExpression(
            '/FOREIGN KEY \(c_category_id\)\s+REFERENCES t_category \(c_id\)/',
            $sql
        );
    }

    public function testSchemaCreatesAndRoundTripsAgainstRealDatabase(): void
    {
        $em = $this->entityManager();
        $metadata = $em->getMetadataFactory()->getAllMetadata();

        // Actually build the schema in the in-memory database. If any prefixed
        // name were inconsistent, this DDL would fail to execute.
        (new SchemaTool($em))->createSchema($metadata);

        $category = new Category();
        $category->name = 'Tech';

        $tag = new Tag();
        $tag->label = 'symfony';

        $article = new Article();
        $article->title = 'Hello';
        $article->category = $category;
        $article->tags->add($tag);

        $em->persist($category);
        $em->persist($tag);
        $em->persist($article);
        $em->flush();

        $articleId = $article->id;
        $em->clear();

        // Reload through the prefixed columns / join table: proves INSERT and
        // SELECT both resolve the same physical names at runtime.
        $reloaded = $em->find(Article::class, $articleId);

        self::assertNotNull($reloaded);
        self::assertSame('Hello', $reloaded->title);
        self::assertNotNull($reloaded->category);
        self::assertSame('Tech', $reloaded->category->name);
        self::assertCount(1, $reloaded->tags);
        self::assertSame('symfony', $reloaded->tags->first()->label);
    }
}
