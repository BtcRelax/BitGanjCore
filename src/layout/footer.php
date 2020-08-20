<script src="js/main.js"></script>
  <script>
        const identificator = document.getElementById('identificator');

        identificator.addEventListener('click', function() {
           bitganj.fetchIdentificator('telegramIdIdent');
        });
  </script>
  <script>
	if ('serviceWorker' in navigator) {
	  window.addEventListener('load', function() {
	    navigator.serviceWorker.register('service-worker.js')
	      .then(reg => {
	        console.log('Service worker registered!', reg);
	      })
	      .catch(err => {
	        console.log('Service worker registration failed: ', err);
	      });
	  });
	} else {
        console.log('Service workers are not supported.');
    }
	</script>
  <script>
    let deferredPrompt;
    window.addEventListener('beforeinstallprompt', event => {

      // Prevent Chrome 67 and earlier from automatically showing the prompt
      event.preventDefault();

      // Stash the event so it can be triggered later.
      deferredPrompt = event;

      // Attach the install prompt to a user gesture
      document.querySelector('#installBtn').addEventListener('click', event => {

        // Show the prompt
        deferredPrompt.prompt();

        // Wait for the user to respond to the prompt
        deferredPrompt.userChoice
          .then((choiceResult) => {
            if (choiceResult.outcome === 'accepted') {
              console.log('User accepted the A2HS prompt');
            } else {
              console.log('User dismissed the A2HS prompt');
            }
            deferredPrompt = null;
          });
      });

      // Update UI notify the user they can add to home screen
      document.querySelector('#installBanner').style.display = 'flex';
    });
  </script>
