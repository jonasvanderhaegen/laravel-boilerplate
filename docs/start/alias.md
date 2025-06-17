
🛳️ Optional: Create a sail alias (recommended)
To avoid typing ./vendor/bin/sail every time, add a simple alias to your shell config:

🪟 **Apple users**:

For bash, zsh, or most Unix shells:
```bash
alias sail='[ -f ./vendor/bin/sail ] && bash ./vendor/bin/sail || echo "Sail not available"'
```

Add this to your shell config file:

| Shell | Config file                              |
|-------|------------------------------------------|
| Bash  | `~/.bashrc` or `~/.bash_profile`         |
| Zsh   | `~/.zshrc`                               |
| Fish  | `~/.config/fish/config.fish` (see below) |


Then reload your shell:

```bash
source ~/.bashrc  # or ~/.zshrc
```

Now you can run Sail like this:

```bash
sail up -d
sail artisan migrate
sail npm run dev
```

🐟 Fish shell?
Use this instead:

```fish
alias sail 'bash ./vendor/bin/sail'
```

🪟 **Windows users**:

- ✅ Using Git Bash or WSL? Add this to `~/.bashrc`:

```
alias sail='[ -f ./vendor/bin/sail ] && bash ./vendor/bin/sail'
```

- ✅ Using PowerShell? Add this to your PowerShell profile (`$PROFILE`):
```powershell
function sail { & "./vendor/bin/sail" $args }
```
