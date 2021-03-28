<?php
use Discord\Discord;
use Discord\Parts\Channel\Message;
use Psr\Http\Message\ResponseInterface;
use React\EventLoop\Factory;
use React\Http\Browser;

use Symfony\Component\DomCrawler\Crawler;

require __DIR__ . '/vendor/autoload.php';

$loop = Factory::create();

$browser = new Browser($loop);

$discord = new Discord([
    'token' => getenv('TOKEN_DISCORD'),
    'loop' => $loop,
]);


$discord->on('message', function(Message $message, Discord $discord) use ($browser) {
    echo "Bot is ready!", PHP_EOL;


    if(str_starts_with(strtolower($message->content),'!discipline')) {
        echo "{$message->author->username}: {$message->content}",PHP_EOL;

        preg_match("/^\!discipline (.*)\:([0-9])$/", $message->content, $output_array);
    
        $discipline = $output_array[1];
        $level = $output_array[2];

        echo $url = 'https://guidetothemasquerade.weebly.com/'. $discipline .'.html';
        $browser->get($url)->then(function(ResponseInterface $response)use($message, $level){
                        
            $crawler = new Crawler(((string) $response->getBody()));

            $discipline = $crawler->filterXPath(
                './/*[contains(concat(" ",normalize-space(@class)," ")," paragraph ")]//ul//li[(count(preceding-sibling::*)+1) = ' . $level .']'
            )->text();
            $message->reply($discipline);
        });
    }
});

$discord->run();