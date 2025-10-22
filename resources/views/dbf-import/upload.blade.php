<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Upload DBF</title>
</head>
<body>
    <h1>Upload DBF - SIAGAS / RIMAS</h1>

    <form id="uploadForm" enctype="multipart/form-data">
        <label>Origem:</label>
        <select name="source" required>
            <option value="siagas">SIAGAS</option>
            <option value="rimas">RIMAS</option>
        </select>
        <br><br>

        <label>Arquivo ZIP:</label>
        <input type="file" name="file" accept=".zip" required>
        <br><br>

        <button type="submit">Upload</button>
    </form>

    <div id="result" style="margin-top: 20px;"></div>

    <script>
        document.getElementById('uploadForm').addEventListener('submit', async function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            const resultDiv = document.getElementById('result');

            resultDiv.innerHTML = 'Processando...';

            try {
                const response = await fetch('/dbf-import/upload', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });

                const result = await response.json();

                if (result.success) {
                    resultDiv.innerHTML = `
                        <strong>Sucesso!</strong><br>
                        Origem: ${result.source}<br>
                        Arquivo detectado: ${result.detected_dbf_file}<br>
                        Total importado: ${result.total_imported} registros<br>
                        Duração: ${result.duration_seconds}s
                    `;
                } else {
                    resultDiv.innerHTML = `<strong>Erro:</strong> ${result.message}`;
                }

            } catch (error) {
                resultDiv.innerHTML = `<strong>Erro:</strong> ${error.message}`;
            }
        });
    </script>
</body>
</html>
