<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HackGuardian</title>
    <!-- Tailwind via CDN (for quick prototyping) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* Base resets */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Inter, system-ui, -apple-system, 'Segoe UI', Roboto, 'Helvetica Neue', Arial; }
        .header { background: #333; color: white; padding: 15px; }
        .nav { display: flex; justify-content: space-between; }
        .nav-links a { color: white; margin-left: 20px; text-decoration: none; }
        .container { max-width: 1200px; margin: 20px auto; padding: 20px; }
        .btn { padding: 8px 16px; background: blue; color: white; border: none; cursor: pointer; }
        .btn-danger { background: red; }
        .btn-success { background: green; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .form-group { margin-bottom: 15px; }
        input, textarea, select { width: 100%; padding: 8px; margin-top: 5px; }
        .alert { padding: 10px; margin: 10px 0; border-radius: 4px; }
        .alert-success { background: #d4edda; color: #155724; }
        .alert-error { background: #f8d7da; color: #721c24; }

        /* Fire effect styles */
        .fire-wrap { display:flex; justify-content:center; margin:12px 0; }
        .fire { position:relative; width:6rem; height:8rem; filter:drop-shadow(0 8px 16px rgba(255,140,0,0.25)); }
        .flame { position:absolute; left:50%; bottom:0; transform-origin:50% 100%; border-radius:50% 50% 45% 45%; opacity:0.9; }
        .flame.one  { width:2.6rem; height:4.6rem; margin-left:-1.3rem; background: radial-gradient(60% 60% at 50% 40%, #fff176 0%, #ffd54f 20%, #ffb74d 45%, transparent 70%); animation: rise 1.8s infinite ease-in-out, flicker 0.25s infinite; }
        .flame.two  { width:2rem;  height:3.6rem; margin-left:-1rem; background: radial-gradient(60% 60% at 50% 40%, #ffd54f 0%, #ff8a50 30%, #ff5722 60%, transparent 80%); animation: rise 1.5s infinite ease-in-out, flicker 0.2s infinite; }
        .flame.three{ width:1.2rem; height:2.4rem; margin-left:-0.6rem; background: radial-gradient(60% 60% at 50% 40%, #ff6e40 0%, #ff3d00 40%, transparent 70%); animation: rise 1.2s infinite ease-in-out, flicker 0.18s infinite; }
        .embers { position:absolute; bottom:-6px; left:50%; width:0; height:0; }
        .ember { position:absolute; background:rgba(255,200,80,0.9); width:6px; height:6px; border-radius:50%; animation: emberUp 1.6s infinite ease-in-out; }

        @keyframes rise {
            0% { transform: translateY(0) scaleY(1) }
            50% { transform: translateY(-6px) scaleY(1.03) }
            100% { transform: translateY(0) scaleY(1) }
        }
        @keyframes flicker {
            0% { opacity:0.95; transform:translateX(0) rotate(0deg) }
            25% { opacity:0.85; transform:translateX(-2px) rotate(-2deg) }
            50% { opacity:0.9; transform:translateX(2px) rotate(1deg) }
            75% { opacity:0.8; transform:translateX(-1px) rotate(-1deg) }
            100% { opacity:0.95; transform:translateX(0) rotate(0deg) }
        }
        @keyframes emberUp {
            0% { transform: translate(-50%,0) scale(0.6); opacity:1 }
            70% { transform: translate(-50%,-40px) scale(0.8); opacity:0.6 }
            100% { transform: translate(-50%,-60px) scale(1); opacity:0 }
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="nav">
            <h1>HackGuardian</h1>
            <div class="nav-links">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <span>Welcome, <?php echo $_SESSION['username']; ?> (<?php echo $_SESSION['user_role']; ?>)</span>
                    <a href="/?action=logout">Logout</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="container">
