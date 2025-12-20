<?php require_once __DIR__ . '/../shared/header.php'; ?>

<h2>Register</h2>

<?php if (isset($error)): ?>
    <div class="alert alert-error"><?php echo $error; ?></div>
<?php endif; ?>

<form method="POST" action="">
    <div class="form-group">
        <label>Username:</label>
        <input type="text" name="username" required>
    </div>
    
    <div class="form-group">
        <label>Email:</label>
        <input type="email" name="email" required>
    </div>
    
    <div class="form-group">
        <label>Password:</label>
        <input type="password" name="password" required>
    </div>
    
    <div class="form-group">
        <label>Role:</label>
        <select name="role">
            <option value="user">User</option>
            <option value="hacker">Hacker</option>
            <option value="pentester">Pentester</option>
            <option value="engineer">Engineer</option>
            <option value="admin">Admin</option>
        </select>
    </div>
    
    <button type="submit" class="btn">Register</button>
</form>

<p>Already have an account? <a href="/?action=login">Login here</a></p>

<?php require_once __DIR__ . '/../shared/footer.php'; ?>