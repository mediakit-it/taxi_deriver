<!DOCTYPE html>
<head>
  <title>Pusher Test</title>
  <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
  <script>
    // Enable pusher logging - don't include this in production
    Pusher.logToConsole = true;

    var pusher = new Pusher('53901edd70c280d59a2f', {
      cluster: 'mt1'
    });

    pusher.connection.bind('connected', function() {
      console.log('Connected to Pusher');
    });

    var channel = pusher.subscribe('admin');
    channel.bind('pusher:subscription_succeeded', function() {
      console.log('Successfully subscribed to channel');
    });

    channel.bind('pusher:subscription_error', function(status) {
      console.log('Subscription error:', status);
    });

    channel.bind('sendMailAdmin', function(data) {
      alert(JSON.stringify(data));
    });
  </script>
</head>
<body>
  <h1>Pusher Test</h1>
  <p>
    Try publishing an event to channel <code>my-channel</code>
    with event name <code>my-event</code>.
  </p>
</body>