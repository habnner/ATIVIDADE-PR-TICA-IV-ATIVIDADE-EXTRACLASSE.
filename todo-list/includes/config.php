<?php
// Define as constantes de conexão com o banco de dados
define('DB_SERVER', 'sql107.infinityfree.com');
define('DB_USERNAME', 'if0_40530005');
define('DB_PASSWORD', 'habnner123');
define('DB_NAME', 'if0_40530005_todoweb');

// URL base para redirecionamentos
define('BASE_URL', 'http://todo-listlaura.infinityfreeapp.com');

// Inicia a sessão se ainda não tiver sido iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Cria e retorna uma nova conexão com o banco de dados MySQL.
 * Usa as constantes definidas acima.
 * @return mysqli A conexão estabelecida.
 * @throws Exception Se a conexão falhar.
 */
function getConnection() {
    // Tenta criar a conexão usando as constantes
    $conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

    // Verifica a conexão
    if ($conn->connect_error) {
        // --- MODO DEBUG CRÍTICO ---
        // Exibe o erro específico do MySQL (REMOVER EM PRODUÇÃO)
        die("ERRO DE CONEXÃO CRÍTICO: Falha ao conectar ao MySQL. Detalhes: " . $conn->connect_error . 
            ". Verifique suas credenciais no arquivo config.php. A aplicação não pode continuar sem o banco de dados.");
        // --- FIM DO DEBUG ---
    }
    
    // Define o charset para UTF8 para lidar com caracteres especiais
    $conn->set_charset("utf8mb4");
    
    return $conn;
}

/**
 * Função utilitária para fechar a conexão, se estiver aberta.
 * @param mysqli|null $conn A conexão a ser fechada.
 */
function closeConnection(?mysqli $conn) {
    if ($conn && $conn->ping()) {
        $conn->close();
    }
}
?>