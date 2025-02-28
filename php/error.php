<?php require "../config/session.php"; ?>
<!DOCTYPE html>
<html lang="fr">
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
            0% { transform: translateX(-20px); }
            100% { transform: translateX(20px); }
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
    <div class="cloud" style="top: 20%; left: 10%;"></div>
    <div class="cloud" style="top: 40%; right: 15%; animation-delay: 2s;"></div>
    <div class="cloud" style="top: 60%; left: 20%; animation-delay: 4s;"></div>
    
    <div class="message">
        <h1>Oups ! Tempête en vue...</h1>
        <p>Erreur 404 : Il semble que cette page se soit évaporée comme une goutte d'eau au soleil.</p>

    </div>
</body>
</html>