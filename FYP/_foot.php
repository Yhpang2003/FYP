    </main> 

    <footer class="bg-gray-800 text-white py-8 mt-12">
        <div class="container mx-auto px-6 text-center">
            <p>&copy; <?php echo date("Y"); ?> DIY E-Commerce Store. All Rights Reserved.</p>
            <p class="text-gray-400 mt-2">A Final Year Project.</p>
            <div class="mt-4">
                <a href="#" class="text-gray-400 hover:text-white mx-2"><i class="fab fa-facebook-f"></i></a>
                <a href="#" class="text-gray-400 hover:text-white mx-2"><i class="fab fa-twitter"></i></a>
                <a href="#" class="text-gray-400 hover:text-white mx-2"><i class="fab fa-instagram"></i></a>
            </div>
        </div>
    </footer>

    <script>
        // Wait for the entire page to load before running the script
        document.addEventListener('DOMContentLoaded', function() {
            // Find the flash message element on the page
            const flashMessage = document.querySelector('.flash-message');

            // If a flash message exists...
            if (flashMessage) {
                setTimeout(() => {
                    flashMessage.classList.add('fade-out');
                }, 3000);


                setTimeout(() => {
                    if (flashMessage) {
                        flashMessage.remove();
                    }
                }, 3500); // 3.5 seconds, just after the fade ends
            }
        });
    </script>
</body>
</html>
