<form method="POST" action="{{ route('password.recovery.send') }}">
    @csrf
    <input type="email" name="email" placeholder="Tu correo" required>
    <button type="submit">Enviar código</button>
</form>
