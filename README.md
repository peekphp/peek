# Peek

Peek is a lightweight and powerful PHP package that integrates with any AI Client to provide detailed insights, improvements, and optimization suggestions for your PHP files and code snippets.

> **Requires [PHP 8.3+](https://php.net/releases/)**

---

## ✨ Key Features

- 🧠 Analyze PHP files or snippets with DeepSeek for actionable improvement suggestions.
- 🔍 Spot inefficiencies, errors, and optimization opportunities in your codebase.
- ⚡ Easily integrate into your existing workflows via a command-line interface.

---

## 🚀 Installation

Install Peek using [Composer](https://getcomposer.org):

```bash
composer require peekphp/peek
```

---

## ⚙️ Usage

### Analyze a PHP File
Run the `peek` command to analyze an entire PHP file:
```bash
vendor/bin/peek peek path/to/your/file.php
```

### Analyze a Code Snippet
Specify a line range within a file to analyze just a part of your code:
```bash
vendor/bin/peek peek path/to/your/file.php --lines=10:20
```

### Example Output:
```bash
Analyzing the file: path/to/your/file.php

Suggestions:
1. Add strict types to improve type safety.
2. Replace deprecated function `md5()` with a modern hashing algorithm like `password_hash()`.
3. Optimize nested loops for better performance.
```

---

## 🔧 Configuration

To set up Peek in your project, use the `init` command:
```bash
vendor/bin/peek init
```

This will generate a peek.json configuration file in your project's root directory, allowing you to define client settings such as API key, URL, and model.

Example peek.json:

```json
{
    "client": {
        "key": "your-api-key",
        "url": "https://api.example.com",
        "model": "gpt-4"
    }
}
```

---

## 🤦🏻 Run Tests

Run the complete test suite using PHPUnit:
```bash
composer test
```

Peek comes with both unit and integration tests to ensure its reliability.

---

## 🤝 Contributing

Contributions are welcome! Feel free to submit issues or pull requests to help make Peek even better.

### Steps to Contribute:
1. Fork the repository.
2. Create a feature branch:
   ```bash
   git checkout -b feature/your-feature-name
   ```
3. Commit your changes:
   ```bash
   git commit -m "Add your feature description"
   ```
4. Push to your branch:
   ```bash
   git push origin feature/your-feature-name
   ```
5. Submit a pull request.

We appreciate your contributions! ❤️

---

## 📚 License

Peek is open-source software licensed under the [MIT License](LICENSE).

---

Crafted with ❤️ by [Doekos](https://github.com/doekos)

