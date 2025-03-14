<?php require "../config/session.php"; ?>
<!DOCTYPE html>
<html lang="fr">
<link rel="stylesheet" href="../includes/style.css" type="text/css" />
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Erreur 404 - Page Introuvable</title>
    <style>
        body {
            height: 100vh;
            margin: 0;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            font-family: Arial, sans-serif;
            background: linear-gradient(to bottom, #87CEEB, #4682B4);
            color: white;
            text-align: center;
            position: relative;
        }

        .message {
            background: rgba(255, 255, 255, 0.9);
            color: #333;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
            max-width: 500px;
            position: relative;
            z-index: 1;
        }

        .btn-home {
            display: inline-block;
            margin-top: 15px;
            padding: 10px 20px;
            background: #0000FF;
            color: #333;
            font-weight: bold;
            text-decoration: none;
            border-radius: 5px;
            transition: 0.3s;
        }

        .btn-home:hover {
            background: #0000CC;
        }
    </style>
</head>
<body>
    <div class="cloud" style="top: 18%; left: 10%;"></div>
    <div class="cloud" style="top: 40%; right: 15%;"></div>
    <div class="cloud" style="top: 80%; left: 20%;"></div>
    
    <div class="message">
        <h1>Oups ! Tempête en vue...</h1>
        <p>Erreur 404 : Il semble que cette page se soit évaporée comme une goutte d'eau au soleil.</p>
    </div>
</body>
</html>