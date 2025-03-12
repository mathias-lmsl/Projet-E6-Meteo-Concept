<?php
require "../config/session.php";
?>
<link rel="stylesheet" href="../includes/style.css" type="text/css" />
<link rel="stylesheet" href="../includes/stylelog.css" type="text/css" />
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Se connecter</title>
    <!--<style>
        body {
            height: 100vh;
            margin: 0;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            font-family: Arial, sans-serif;
            background: linear-gradient(to bottom, #87CEEB, #4682B4);
            padding: 20px;
            box-sizing: border-box;
        }

        h2 {
            text-align: center;
            margin-bottom: 20px;
        }

        .container {
            text-align: center;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 450px;
        }

        form {
            display: flex;
            flex-direction: column;
            align-items: center;
            width: 100%;
        }

        input[type="text"],
        input[type="password"] {
            padding: 10px;
            margin: 5px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
            width: 100%;
            max-width: 300px;
            box-sizing: border-box;
        }

        input[type="submit"] {
            padding: 10px 20px;
            margin-top: 10px;
            border: none;
            background-color: #007BFF;
            color: white;
            border-radius: 5px;
            cursor: pointer;
            width: 100%;
            max-width: 300px;
        }

        input[type="submit"]:hover {
            background-color: #0056b3;
        }

        @media (max-width: 480px) {
            .container {
                padding: 15px;
            }

            input[type="text"],
            input[type="password"],
            input[type="submit"] {
                max-width: 100%;
            }
        }
    /*------------------------------------------------------*/
        .cloud {
            position: absolute;
            width: 100px;
            height: 60px;
            background: white;
            border-radius: 50px;
            box-shadow: 0px 5px 10px rgba(0, 0, 0, 0.1);
            animation: float 6s infinite alternate ease-in-out;
            z-index: -1;
        }

        .cloud::before, .cloud::after {
            content: "";
            position: absolute;
            background: white;
            border-radius: 50%;
        }

        .cloud::before {
            width: 70px;
            height: 70px;
            top: -35px;
            left: 10px;
        }

        .cloud::after {
            width: 50px;
            height: 50px;
            top: -25px;
            right: 10px;
        }

        @keyframes float {
            0% { transform: translateX(-30px); }
            100% { transform: translateX(30px); }
        }
    </style>-->
</head>
<body>
    <div class="cloud" style="top: 20%; left: 10%;"></div>
    <div class="cloud" style="top: 40%; right: 15%;"></div>
    <div class="cloud" style="top: 70%; left: 20%;"></div>
    <div class="container">
        <h2>Se connecter</h2>
        <?php
        if (!empty($_GET["page"])){
            switch ($_GET["page"]) {
                case 1:
                    echo '<form action="login.php?page='. $_GET["page"] .'" method="POST">';
                    echo '<input type="text" id="login" name="login" placeholder="Nom d\'utilisateur" required> <br>';
                    echo '<input type="password" id="mdp" name="mdp" placeholder="Mot de passe" required> <br>';
                    echo '<input type="submit" value="Connexion">';
                    echo '</form>';
                    if (!empty($_GET["error"])) {
                        if ($_GET["error"] == 1) echo '<br><span style="color:red;">Login/mot de passe incorrect</span>';
                    }
                    break;
                case 2:
                    if (!empty($_GET["id"])){
                        echo '<form action="login.php?id=' . $_GET["id"] .'&page='. $_GET["page"] .'" method="POST">';
                        echo '<input type="text" id="login" name="login" placeholder="Nom d\'utilisateur" required> <br>';
                        echo '<input type="password" id="mdp" name="mdp" placeholder="Mot de passe" required> <br>';
                        echo '<input type="submit" value="Connexion">';
                        echo '</form>';
                        if (!empty($_GET["error"])) {
                            if ($_GET["error"] == 1) echo '<br><span style="color:red;">Login/mot de passe incorrect</span>';
                        }
                    }
                    else echo '<p style="color:red;">Le QR code que vous venez de scanner n\'est pas valide !<br>Veuillez réessayer ou scanner un nouveau QR code</p>';
                    break;
                default:
                    header('Location: error.php');
                    exit;
            }
        }else {
            header('Location: error.php');
            exit;
        }
        ?>
    </div>
</body>
</html>
