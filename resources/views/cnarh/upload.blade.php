<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Upload CSV CNARH</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
        }
        form {
            background: #f5f5f5;
            padding: 20px;
            border-radius: 8px;
        }
        input[type="file"] {
            margin: 10px 0;
        }
        button {
            background: #007bff;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        button:hover {
            background: #0056b3;
        }
        #result {
            margin-top: 20px;
            padding: 15px;
            border-radius: 4px;
        }
        .success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .processing {
            background: #fff3cd;
            color: #856404;
            border: 1px solid #ffeeba;
        }
    </style>
</head>
<body>
    <h1>Upload CSV CNARH</h1>

    <form id="uploadForm" enctype="multipart/form-data">
        <label>Arquivo CSV:</label>
        <input type="file" name="file" accept=".csv,.txt" required>
        <br><br>

        <button type="submit">Fazer Upload e Importar</button>
    </form>

    <div id="result"></div>

    <script>
        document.getElementById('uploadForm').addEventListener('submit', async function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            const resultDiv = document.getElementById('result');

            resultDiv.className = 'processing';
            resultDiv.innerHTML = 'Processando arquivo... Isso pode levar alguns minutos.';

            try {
                const response = await fetch('/cnarh/upload', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });

                const result = await response.json();

                if (result.success) {
                    resultDiv.className = 'success';
                    resultDiv.innerHTML = `
                        <strong>Importação concluída com sucesso!</strong><br><br>
                        Total de registros importados: ${result.total_imported}<br>
                        Duração: ${result.duration_seconds}s<br>
                        Mensagem: ${result.message}
                    `;
                } else {
                    resultDiv.className = 'error';
                    resultDiv.innerHTML = `<strong>Erro:</strong> ${result.message}`;
                }

            } catch (error) {
                resultDiv.className = 'error';
                resultDiv.innerHTML = `<strong>Erro na requisição:</strong> ${error.message}`;
            }
        });
    </script>
</body>
</html>
