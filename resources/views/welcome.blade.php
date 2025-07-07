<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Gestion de Flotte - Plateforme Professionnelle</title>
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,600" rel="stylesheet">
    <style>
        html, body {
            background-color: #f8fafc;
            color: #22223b;
            font-family: 'Nunito', sans-serif;
            font-weight: 200;
            height: 100vh;
            margin: 0;
        }
        .full-height {
            height: 100vh;
        }
        .flex-center {
            align-items: center;
            display: flex;
            justify-content: center;
        }
        .position-ref {
            position: relative;
        }
        .content {
            text-align: center;
        }
        .title {
            font-size: 64px;
            font-weight: bold;
            color: #2563eb;
            margin-bottom: 20px;
        }
        .subtitle {
            font-size: 28px;
            color: #495057;
            margin-bottom: 30px;
        }
        .icon {
            font-size: 80px;
            color: #2563eb;
            margin-bottom: 20px;
        }
        .footer {
            position: absolute;
            bottom: 20px;
            width: 100%;
            text-align: center;
            color: #adb5bd;
        }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="flex-center position-ref full-height">
        <div class="content">
            <div class="icon">
                <i class="fas fa-truck-moving"></i>
            </div>
            <div class="title">
                Gestion de Flotte
            </div>
            <div class="subtitle">
                Plateforme professionnelle pour la gestion, le suivi et l'optimisation de votre parc automobile.<br>
                <span style="color:#2563eb; font-weight:bold;">Monnaie : FCFA (XOF)</span>
            </div>
        </div>
        <div class="footer">
            &copy; {{ date('Y') }} Gestion de Flotte. Tous droits réservés.
        </div>
    </div>
</body>
</html>
