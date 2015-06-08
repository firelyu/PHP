<?php

    const ISBN_SEARCH_URL = 'http://api.douban.com/v2/book/isbn/';
    const ISBN_LIST_FILE = 'isbn_list.txt';

    // DB const
    const DB_HOST = 'localhost';
    const DB_USER = 'root';
    const DB_PASSWORD = 'xxxxxx';
    const DB_NAME = 'xuexiao';
    const DB_TABLE_NAME = 'hywgj_library_books';
    const DB_VALUE = '(author, title, isbn13, image, publisher, pubdate, price, pages, summary, author_intro, token) ';
    const TOKEN = 'bclhwf1433405165';

    function getOneBookByISBN($isbn) {
        $isbnUrl = ISBN_SEARCH_URL . $isbn;
        // echo $isbnUrl . "\n";

        $ch = curl_init($isbnUrl);
        // curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        curl_close($ch);
        //echo $result;

        $jsonContent = json_decode($result);
        // print_r($jsonContent);  

        return $jsonContent;
    }

    // author, title, isbn13, image, publisher, pubdate, price, pages, summary, author_intro, token
    function parseOneBook($jsonObj, $token) {
        $author = NULL;
        foreach ($jsonObj->author as $one) {
            $author .= "$one ";
        }

        $title = $jsonObj->title;
        $isbn13 = $jsonObj->isbn13;

        $image = $jsonObj->images->large;
        // echo $image;
        $publisher = $jsonObj->publisher;
        $pubdate = $jsonObj->pubdate;
        $price = $jsonObj->price;
        $pages = $jsonObj->pages;

        $summary = $jsonObj->summary;
        $summary = substr($summary, 0, strlen($summary) < 500 ? strlen($summary) : 500);
        // $summary = preg_replace('/[\x{10000}-\x{10FFFF}]/u', '', $summary);
        
        $author_intro = $jsonObj->author_intro;
        $author_intro = substr($author_intro, 0, strlen($author_intro) < 500 ? strlen($author_intro) : 500);
        // $author_intro = preg_replace('/[\x{10000}-\x{10FFFF}]/u', '', $author_intro);

        $sql = "('$author', '$title', '$isbn13', '$image', '$publisher', '$pubdate', '$price', '$pages', '$summary', '$author_intro', '$token')";

        return $sql;
    }

    function getISBNListFromFile($file) {
        $content = file_get_contents($file);
        return $content;
    }
    
    function main() {
        $isbnList = explode(",", getISBNListFromFile(ISBN_LIST_FILE));

        $db = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

        $db->set_charset('utf8mb4');

        $insertSql = "INSERT INTO " . DB_TABLE_NAME . " ";
        $insertSql .= DB_VALUE;
        $insertSql .= 'VALUES ';
        $values = NULL;
        foreach ($isbnList as $isbn) {
            $jsonObj = getOneBookByISBN($isbn);
            sleep(8);

            $values .= parseOneBook($jsonObj, TOKEN);
            $values .= ", ";
        }
        $values1 = substr($values, 0, -2);
        $values1 .= ";";
        $insertSql .= $values1;
        echo "$insertSql\n";

        if ($db->query($insertSql) == TRUE) {
            echo "Insert successful.\n";
        } else {
            echo "Insert failed.\n";
            echo $db->error . "\n";
        }

        $db->close();
    }
    
    main();
?>
