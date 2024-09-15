<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <link rel="stylesheet" href="./loginstyles.css">
    <link rel="icon" type="image/x-icon" href="./assets/favico.png">
</head>
<body>

    

    <div class="page">


        <div id="profile">
            <a  href="https://extel.net.br/"> <img
              src="./assets/extel.png"
              title="Pirecal"
            > </a>


        <form method="POST" action="server.php" class="formLogin">
            <p>Bem-vindo ao sistema de gerenciamento de chaves VPN.</p>
            <h1>Login</h1>
            
            
            <input placeholder="Usuário" autocomplete="off"  type="text" id="username" name="username" required>
      
            <input placeholder="Senha" type="password" maxlength="8" id="password" name="password" required>
            <div id="icon" onclick="showHide()"></div>
            <input id="button" type="submit" value="Acessar" class="btn" />
        </form>
    </div>

    <script src="icoeye.js"></script>

    <footer>
        <p>&copy; 2024 - Ryan Ribeiro Oliveira.</p>
    </footer>
</body>
</html>