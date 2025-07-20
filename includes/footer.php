<style>
/* Footer PRO - Élégant et fonctionnel */
footer {
    background: linear-gradient(145deg, #1a2a3a, #0f1926);
    color: #ffffff;
    padding: 3rem 0 1.5rem;
    font-family: 'Montserrat', 'Segoe UI', Roboto, sans-serif;
    position: relative;
    margin-top: 5rem;
    box-shadow: 0 -5px 30px rgba(0, 0, 0, 0.2);
    border-top: 1px solid rgba(255, 255, 255, 0.05);
}

/* Effet de vague sophistiqué */
footer::before {
    content: '';
    position: absolute;
    top: -25px;
    left: 0;
    width: 100%;
    height: 25px;
    background: url("data:image/svg+xml,%3Csvg viewBox='0 0 1200 120' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M0,0V46.29c47.79,22.2,103.59,32.17,158,28,70.36-5.37,136.33-33.31,206.8-37.5C438.64,32.43,512.34,53.67,583,72.05c69.27,18,138.3,24.88,209.4,13.08,36.15-6,69.85-17.84,104.45-29.34C989.49,25,1113-14.29,1200,52.47V0Z' fill='%231a2a3a'/%3E%3C/svg%3E");
    background-size: cover;
    background-repeat: no-repeat;
}

.container {
    width: 90%;
    max-width: 1200px;
    margin: 0 auto;
    display: flex;
    flex-direction: column;
    align-items: center;
}

/* Contenu principal */
.footer-content {
    display: flex;
    justify-content: space-between;
    width: 100%;
    margin-bottom: 2rem;
    flex-wrap: wrap;
    gap: 2rem;
}

.footer-section {
    flex: 1;
    min-width: 200px;
    padding: 0 1rem;
}

.footer-section h3 {
    color: #f8d56b;
    margin-bottom: 1.5rem;
    font-size: 1.2rem;
    position: relative;
    padding-bottom: 0.5rem;
}

.footer-section h3::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    width: 50px;
    height: 2px;
    background: linear-gradient(90deg, #3498db, transparent);
}

.footer-section ul {
    list-style: none;
    padding: 0;
    margin: 0;
}

.footer-section li {
    margin-bottom: 0.8rem;
}

.footer-section a {
    color: rgba(255, 255, 255, 0.7);
    text-decoration: none;
    transition: all 0.3s ease;
    display: inline-block;
    position: relative;
}

.footer-section a:hover {
    color: #ffffff;
    transform: translateX(5px);
}

.footer-section a::after {
    content: '';
    position: absolute;
    bottom: -2px;
    left: 0;
    width: 0;
    height: 1px;
    background: #3498db;
    transition: width 0.3s ease;
}

.footer-section a:hover::after {
    width: 100%;
}

/* Copyright et bas de footer */
.footer-bottom {
    width: 100%;
    text-align: center;
    padding-top: 1.5rem;
    border-top: 1px solid rgba(255, 255, 255, 0.1);
    margin-top: 1.5rem;
}

.footer-bottom p {
    margin: 0;
    color: rgba(255, 255, 255, 0.6);
    font-size: 0.9rem;
    letter-spacing: 0.5px;
}

/* Effets d'animation */
footer {
    opacity: 0;
    transform: translateY(20px);
    animation: fadeInUp 0.6s 0.3s ease-out forwards;
}

@keyframes fadeInUp {
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Responsive Design */
@media (max-width: 768px) {
    .footer-content {
        flex-direction: column;
        gap: 2.5rem;
    }
    
    .footer-section {
        min-width: 100%;
    }
    
    footer::before {
        top: -15px;
        height: 15px;
    }
}
</style>

<footer>
    <div class="container">
        <div class="footer-content">
            <div class="footer-section">
                <h3>Navigation</h3>
                <ul>
                    <li><a href="../phone shop/index.php">Accueil</a></li>
                    <li><a href="../phone shop/cart.php">Panier</a></li>
                    <li><a href="../phone shop/login.php">Connexion</a></li>
                    <li><a href="../phone shop/register.php">Inscription</a></li>
                </ul>
            </div>
            
            <div class="footer-section">
                <h3>Légal</h3>
                <ul>
                    <li><a href="#">Politique de confidentialité</a></li>
                    <li><a href="#">Conditions d'utilisation</a></li>
                    <li><a href="#">Préférences cookies</a></li>
                </ul>
            </div>
            
            <div class="footer-section">
                <h3>Contact</h3>
                <ul>
                    <li><a href="mailto:contact@chombocas.com">contact@chombocas.com</a></li>
                    <li><a href="tel:+243 99456789">+243 994 567 489</a></li>
                    <li><a href="#">Formulaire de contact</a></li>
                </ul>
            </div>
        </div>
        
        <div class="footer-bottom">
            <p>&copy; <?php echo date('Y'); ?> Chombo Cas. Tous droits réservés.</p>
        </div>
    </div>
</footer>