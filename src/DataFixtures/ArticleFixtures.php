<?php

namespace App\DataFixtures;

use App\Entity\Article;
use App\Entity\Category;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class ArticleFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $faker = \Faker\Factory::create('en_US');

        // Creation des Categories
        $categories = [];

        for ($i = 0; $i < 5; ++$i) {
            $category = new Category();
            $category->setTitle($faker->sentence())
                    ->setDescription($faker->paragraph());

            $manager->persist($category);

            $categories[] = $category;
        }

        // Creation des Sous-Categories
        $sousCategories = [];

        for ($i = 0; $i < 10; ++$i) {
            $category = new Category();
            $category->setParent($categories[$i % 5])
                    ->setTitle($faker->sentence())
                    ->setDescription($faker->paragraph());

            $manager->persist($category);

            $sousCategories[] = $category;
        }

        // Creation des Sous-Categories
        for ($i = 0; $i < 25; ++$i) {
            $category = new Category();
            $category->setParent($sousCategories[array_rand($sousCategories)])
                ->setTitle($faker->sentence())
                ->setDescription($faker->paragraph());

            $manager->persist($category);

            $sousCategories[] = $category;
        }

        // Creation des Articles
        $categories = array_merge($categories, $sousCategories);

        for ($k = 0; $k < 60; ++$k) {
            $article = new Article();
            $article->setCategory($categories[array_rand($categories)])
                ->setTitle($faker->sentence())
                ->setContent(
                    "<p>" . join($faker->paragraphs(mt_rand(0, 8)), "</p><p>") . "</p>"
                )
                ->setUpdatedAt(new \DateTime());

            $manager->persist($article);
        }

        $manager->flush();
    }
}
