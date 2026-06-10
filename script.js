document.addEventListener('DOMContentLoaded', () => {
    // Smooth scrolling for navigation links
    const navLinks = document.querySelectorAll('.nav-links a, .nav-cta a');
    
    navLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            const targetId = this.getAttribute('href');
            
            // Only apply smooth scroll if it's an anchor link on the same page
            if (targetId.startsWith('#')) {
                e.preventDefault();
                const targetElement = document.querySelector(targetId);
                
                if (targetElement) {
                    // Update active state
                    document.querySelectorAll('.nav-links a').forEach(nav => nav.classList.remove('active'));
                    if (this.classList.contains('nav-links a')) {
                        this.classList.add('active');
                    }

                    // Scroll to target
                    window.scrollTo({
                        top: targetElement.offsetTop - 90, // Adjust for sticky header
                        behavior: 'smooth'
                    });
                }
            }
        });
    });

    // Form submission via fetch
    const forms = document.querySelectorAll('.modern-contact-form');
    forms.forEach(form => {
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            const btn = form.querySelector('button[type="submit"]');
            const originalText = btn.innerHTML;
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Enviando...';
            btn.disabled = true;

            try {
                const formData = new FormData(form);
                const response = await fetch(form.action, {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (response.ok) {
                    alert(result.message || '¡Gracias por tu mensaje! Nos pondremos en contacto pronto.');
                    form.reset();
                } else {
                    alert(result.message || 'Error al enviar el mensaje. Intenta nuevamente.');
                }
            } catch (error) {
                alert('Ocurrió un error al conectarse con el servidor. Intenta nuevamente más tarde.');
                console.error(error);
            } finally {
                btn.innerHTML = originalText;
                btn.disabled = false;
            }
        });
    });

    // Mobile menu toggle
    const mobileMenuToggle = document.querySelector('.mobile-menu-toggle');
    const navLinksContainer = document.querySelector('.nav-links');
    const navCta = document.querySelector('.nav-cta');
    
    if (mobileMenuToggle) {
        mobileMenuToggle.addEventListener('click', () => {
            navLinksContainer.classList.toggle('active');
            navCta.classList.toggle('active');
            const icon = mobileMenuToggle.querySelector('i');
            if(navLinksContainer.classList.contains('active')) {
                icon.classList.remove('fa-bars');
                icon.classList.add('fa-xmark');
            } else {
                icon.classList.remove('fa-xmark');
                icon.classList.add('fa-bars');
            }
        });
    }

    // Tabs functionality
    const tabItems = document.querySelectorAll('.tab-item');
    const tabContents = document.querySelectorAll('.tab-content');

    if (tabItems.length > 0) {
        tabItems.forEach(item => {
            item.addEventListener('click', () => {
                // Remove active class from all tabs
                tabItems.forEach(tab => tab.classList.remove('active'));
                tabContents.forEach(content => content.classList.remove('active'));

                // Add active class to clicked tab
                item.classList.add('active');

                // Show corresponding content
                const targetId = item.getAttribute('data-tab');
                if (targetId) {
                    const targetContent = document.getElementById(targetId);
                    if (targetContent) {
                        targetContent.classList.add('active');
                    }
                }
            });
        });
    }
});
