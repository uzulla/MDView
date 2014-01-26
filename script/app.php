<?php
require __DIR__."/../vendor/autoload.php";

$s_socket_uri = '127.0.0.1:8080';
$s_socket = stream_socket_server('tcp://'.$s_socket_uri, $errno, $errstr, 30) OR
    die("Failed to create socket");

echo "Start Server at {$s_socket_uri}. Waiting connect.\n";

`open http://${s_socket_uri}`; // for mac.

$conn = @stream_socket_accept($s_socket, 60, $peer) ;
stream_set_blocking($conn, 0);
echo "Connected with $peer.  Request info...\n";

$client_request='';
while( !preg_match('/\r?\n\r?\n/', $client_request) )
{
    // discard request;
    $client_request .= fread($conn, 1024);
}

$headers = "HTTP/1.0 200 OK\n"
    ."Server: php\n"
    ."Content-Type: text/html\n"
    ."\n";

$html = "<html><body>";
$html .= "<style>\n".file_get_contents(__DIR__."/../resource/screen.css")."\n</style>\n";

$md_str = file_get_contents($argv[1]);

$me = new \Michelf\MarkdownExtra;
$html .= $me->transform($md_str);

$body = $html;

if ((int) fwrite($conn, $headers . $body) < 1) {
    die("Write to socket failed!");
}
stream_socket_shutdown($conn, STREAM_SHUT_WR);
echo "Finish";

