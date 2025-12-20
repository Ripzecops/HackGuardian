<?php require_once __DIR__ . '/../shared/header.php'; ?>

<h2>Login</h2>

<?php if (isset($error)): ?>
    <div class="alert alert-error"><?php echo $error; ?></div>
<?php endif; ?>

<form method="POST" action="">
    <div class="form-group">
        <label>Email:</label>
        <input type="email" name="email" required>
    </div>
    
    <div class="form-group">
        <label>Password:</label>
        <input type="password" name="password" required>
    </div>
    
    <button type="submit" class="btn">Login</button>
</form>

<p>Don't have an account? <a href="/?action=register">Register here</a></p>

<?php require_once __DIR__ . '/../shared/footer.php'; ?>