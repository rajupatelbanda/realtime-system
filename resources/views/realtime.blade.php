<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Realtime Messages</title>
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <style>
    body{font-family: Arial, Helvetica, sans-serif; padding:20px;}
    #messages{max-width:900px;border:1px solid #eee;padding:10px;height:320px;overflow:auto;}
    .msg{padding:6px;border-bottom:1px solid #f1f1f1;}
  </style>
</head>
<body>
  <h2>Realtime Messages</h2>

  <div id="messages"></div>

  <form id="sendForm">
    <input id="sender_id" placeholder="Sender ID" required style="width:120px" />
    <input id="message" placeholder="Type message" required style="width:600px" />
    <button type="submit">Send</button>
  </form>

  <!-- Pusher JS & Axios via CDN -->
  <script src="https://cdn.jsdelivr.net/npm/pusher-js@7"></script>
  <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>

  <script>
    (function() {
      const KEY = "{{ env('REVERB_APP_KEY') }}";
      const HOST = "{{ env('REVERB_HOST', '127.0.0.1') }}";
      const PORT = "{{ env('REVERB_PORT', 8080) }}";
      const SCHEME = "{{ env('REVERB_SCHEME', 'http') }}";

      // Initialize Pusher client (Reverb implements Pusher protocol)
      const pusher = new Pusher(KEY, {
        wsHost: HOST,
        wsPort: parseInt(PORT),
        wssPort: parseInt(PORT),
        forceTLS: SCHEME === 'https',
        enabledTransports: ['ws', 'wss'],
        disableStats: true,
      });

      const channel = pusher.subscribe('messages.channel');

      channel.bind('message.received', function(payload) {
        const c = document.getElementById('messages');
        const d = document.createElement('div');
        d.className = 'msg';
        d.innerHTML = `<strong>${payload.sender_id}</strong>: ${payload.body} <br><small>${payload.created_at || payload.processed_at}</small>`;
        c.prepend(d);
      });
    })();

    // Send message via API (uses CSRF token for same-origin)
    document.getElementById('sendForm').addEventListener('submit', async function(e) {
      e.preventDefault();
      const sender_id = document.getElementById('sender_id').value;
      const message = document.getElementById('message').value;

      try {
        await axios.post('/api/messages', { sender_id, message }, {
          headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') }
        });
        document.getElementById('message').value = '';
      } catch (err) {
        console.error(err.response?.data || err);
        alert('Error sending message');
      }
    });
  </script>
</body>
</html>
