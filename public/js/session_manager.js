
class SessionManager {
    constructor() {
       
        this.sessionTimeout = 2 * 60 * 60 * 1000; 
        
        this.warningTime = 1 * 60 * 1000; 
        
        
        this.warningTimer = null;
        this.logoutTimer = null;
        
        
        this.alertShown = false;
        
        
        this.init();
    }
    
    init() {
       
        this.checkSession();
        
        
        this.startTimers();
        
        
        this.setupActivityListeners();
    }
    
    checkSession() {
        
        fetch('?pagina=session_check', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'accion=check'
        })
        .then(response => response.json())
        .then(data => {
            if (!data.active) {
                
                window.location.href = '?pagina=login';
            }
        })
        .catch(error => {
            console.error('Error al verificar sesión:', error);
        });
    }
    
    startTimers() {
        
        this.clearTimers();
        
        
        this.warningTimer = setTimeout(() => {
            this.showWarning();
        }, this.sessionTimeout - this.warningTime);
        
        
        this.logoutTimer = setTimeout(() => {
            this.logout();
        }, this.sessionTimeout);
    }
    
    clearTimers() {
        if (this.warningTimer) {
            clearTimeout(this.warningTimer);
            this.warningTimer = null;
        }
        if (this.logoutTimer) {
            clearTimeout(this.logoutTimer);
            this.logoutTimer = null;
        }
    }
    
    showWarning() {
        if (this.alertShown) return;
        
        this.alertShown = true;
        
        Swal.fire({
            title: '¡Atención!',
            html: 'Tu sesión está por expirar en <strong>1 minuto</strong>.<br>¿Deseas continuar con la sesión activa?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sí, continuar',
            cancelButtonText: 'No, cerrar sesión',
            allowOutsideClick: false,
            allowEscapeKey: false,
            timer: 60000, 
            timerProgressBar: true,
            didOpen: () => {
                
                const timer = Swal.getPopup().querySelector('b');
                if (timer) {
                    setInterval(() => {
                        const timeLeft = Swal.getTimerLeft();
                        if (timeLeft) {
                            timer.textContent = Math.ceil(timeLeft / 1000) + ' segundos';
                        }
                    }, 100);
                }
            }
        }).then((result) => {
            if (result.isConfirmed) {
                
                this.renewSession();
            } else if (result.dismiss === Swal.DismissReason.timer) {
                
                this.logout();
            } else {
                
                this.logout();
            }
        });
    }
    
    renewSession() {
        
        fetch('?pagina=session_check', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'accion=renew'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                
                this.alertShown = false;
                this.startTimers();
                
                Swal.fire({
                    title: '¡Sesión renovada!',
                    text: 'Tu sesión ha sido extendida por 2 horas más.',
                    icon: 'success',
                    timer: 2000,
                    showConfirmButton: false
                });
            } else {
                
                this.logout();
            }
        })
        .catch(error => {
            console.error('Error al renovar sesión:', error);
            this.logout();
        });
    }
    
    logout() {
        
        Swal.fire({
            title: 'Sesión cerrada',
            text: 'Tu sesión ha expirado por inactividad.',
            icon: 'info',
            timer: 2000,
            showConfirmButton: false,
            allowOutsideClick: false,
            allowEscapeKey: false
        }).then(() => {
            window.location.href = '?pagina=fin';
        });
    }
    
    setupActivityListeners() {
      
        const events = ['mousedown', 'keypress', 'scroll', 'touchstart', 'click'];
        
        let activityTimeout = null;
        
        const resetActivity = () => {
            
            if (activityTimeout) return;
            
            activityTimeout = setTimeout(() => {
                activityTimeout = null;
                
                
                if (!this.alertShown) {
                    this.updateServerActivity();
                    this.startTimers();
                }
            }, 5000); 
        };
        
       
        events.forEach(event => {
            document.addEventListener(event, resetActivity, true);
        });
    }
    
    updateServerActivity() {
        
        fetch('?pagina=session_check', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'accion=activity'
        })
        .catch(error => {
            console.error('Error al actualizar actividad:', error);
        });
    }
}


document.addEventListener('DOMContentLoaded', function() {
    
    const urlParams = new URLSearchParams(window.location.search);
    const pagina = urlParams.get('pagina');
    
    if (pagina !== 'login' && pagina !== 'fin') {
        window.sessionManager = new SessionManager();
    }
});
