<?php
/**
 * Footer template with flash messages
 */
?>
        </main>
    </div>

    <!-- Flash Messages Container -->
    <div class="flash-messages">
        <?php
        $messages = get_flash_messages();
        foreach ($messages as $msg):
            $class = 'flash-' . $msg['type'];
        ?>
            <div class="flash-message <?php echo $class; ?>">
                <span class="flash-close" onclick="this.parentElement.style.display='none';">&times;</span>
                <?php echo htmlspecialchars($msg['message']); ?>
            </div>
        <?php endforeach; ?>
    </div>

    <script>
        // Auto-hide flash messages after 5 seconds
        document.querySelectorAll('.flash-message').forEach(msg => {
            setTimeout(() => {
                msg.style.display = 'none';
            }, 5000);
        });

        // Delete confirmation
        function confirmDelete() {
            return confirm('Are you sure you want to delete this item? This action cannot be undone.');
        }
    </script>
</body>
</html>
