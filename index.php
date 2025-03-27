<?php 
//versao servidor

//Salve esse arquivo em algum diretorio onde tenha o PHP
//Neste caso eu salvei em /home/sistema/index.php
//Crie um arquivo vazio para guardar os logs em /home/htdocs/sistema/output.txt

//Executar no Linux:
//nohup php /home/htdocs/sistema/index.php >> /home/htdocs/sistema/output.txt &


/*
Para parar a execuçao na porta:
Comando para verificar se alguma porta está sendo executada, no nosso caso o serviço do socket = porta = 7181

Linux:
netstat -lntu

//Comando abaixo mata o serviço que está chamando a po
fuser -k 7181/tcp

Apos isso basta executar o nohup


*/


declare(strict_types=1);
set_time_limit(0);

$file = 'output.txt';

//$current = file_get_contents($file);


//$current .= "T: Data: " . date("d/m/Y H:i:s")."\n";
//file_put_contents($file, $current);


// Queremos que o PHP reporte apenas erros graves, estamos explicitamente ignorando warnings aqui.
// Warnings que, por sinal, acontecem bastante ao se trabalhar com sockets.
error_reporting(E_ERROR | E_PARSE);

// Inicia o servidor na porta 7181
$server = stream_socket_server('tcp://0.0.0.0:7181', $errno, $errstr);

// Em caso de falha, para por aqui.
if ($server === false) {
    fwrite(STDERR, "Error: $errno: $errstr");
    exit(1);
}

// Sucesso, servidor iniciado.
fwrite(STDERR, sprintf("Listening on: %s\n", stream_socket_get_name($server, false)));

// Looping infinito para "escutar" novas conexões
while (true) {
    // Aceita uma conexão ao nosso socket da porta 7181
    // O valor -1 seta um timeout infinito para a função receber novas conexões (socket accept timeout) e isso significa que a execução ficará bloqueada aqui até que uma conexão seja aceita;
    $connection = stream_socket_accept($server, -1, $clientAddress);

    // Se a conexão foi devidamente estabelecida, vamos interagir com ela.
    if ($connection) {
        fwrite(STDERR, "Cliente [{$clientAddress}] Conectado ". date("d/m/Y H:i:s")." \n");

        // Lê 2048 bytes por vez (leitura por "chunks") enquanto o cliente enviar.
        // Quando os dados não forem mais enviados, fread() retorna false e isso é o que interrompe o loop.
        // fread() também retornará false quando o cliente interromper a conexão.
        while ($buffer = fread($connection, 2048)) {
            if ($buffer !== '') {
                // Escreve na conexão do cliente
                //fwrite($connection, "Do Servidor: $buffer");
				fwrite(STDERR, $buffer);
				
				exec("php /home/htdocs/sistema/logica.php '$buffer' ");
				
				//Voce pode pegar no logica.php a variavel $argv[1]
				//A $argv[1] é exatamente o conteudo que está enviando via SOCKET
	
				//echo $buffer;
				
				
				//$current .= $buffer."\n";
				//file_put_contents($file, $current);
            }
        }

        // Fecha a conexão com o cliente
        fclose($connection);
    }
}
