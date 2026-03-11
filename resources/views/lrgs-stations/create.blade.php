<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Nova Estação LRGS | SIMAH</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        @import url("https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap");

        * {
            font-family: "Inter", sans-serif;
            text-decoration: none;
            color: inherit;
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body { height: 100vh; overflow: hidden; }

        .edit-container {
            display: flex;
            width: 100%;
            height: 100vh;
        }

        /* ===== LADO ESQUERDO ===== */
        .edit-form-section {
            flex: 1;
            min-width: 400px;
            max-width: 55%;
            background: #ffffff;
            display: flex;
            flex-direction: column;
            overflow-y: auto;
        }

        .form-scrollable-wrapper {
            flex: 1;
            padding: 40px;
            overflow-y: auto;
        }

        .form-container {
            width: 100%;
            max-width: 560px;
            margin: auto;
        }

        .back-btn-container {
            position: absolute;
            top: 30px;
            right: 30px;
            z-index: 10;
        }

        .back-btn {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            background: transparent;
            color: #165b9c;
            border: 2px solid #165b9c;
            border-radius: 25px;
            font-size: 0.95rem;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .back-btn:hover {
            background: #165b9c;
            color: white;
        }

        .form-header {
            text-align: center;
            margin-bottom: 30px;
            margin-top: 20px;
        }

        .form-header h1 {
            font-size: 2rem;
            color: #333;
            font-weight: 700;
        }

        .form-header p {
            color: #666;
            font-size: 0.95rem;
            margin-top: 6px;
        }

        .form-section-header {
            margin: 25px 0 15px 0;
            padding-bottom: 10px;
            border-bottom: 2px solid #f0f0f0;
        }

        .form-section-header h3 {
            font-size: 1.1rem;
            color: #333;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .form-section-header h3 i { color: #165b9c; }

        .form-group { margin-bottom: 16px; }

        .form-group label {
            display: block;
            margin-bottom: 7px;
            font-weight: 500;
            color: #444;
            font-size: 0.9rem;
        }

        .input-with-icon { position: relative; }

        .input-with-icon i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #165b9c;
            font-size: 1rem;
            z-index: 2;
        }

        .form-control {
            width: 100%;
            padding: 13px 13px 13px 42px;
            border: 1px solid #ccc;
            border-radius: 25px;
            font-size: 0.95rem;
            transition: all 0.3s ease;
            background: #ffffff;
            color: #000;
        }

        .form-control:focus {
            outline: none;
            border-color: #165b9c;
            box-shadow: 0 0 0 3px rgba(22, 91, 156, 0.1);
        }

        .alert-errors {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f1b0b7;
            padding: 14px 18px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 0.9rem;
        }

        .btn-container {
            display: flex;
            gap: 15px;
            margin-top: 30px;
        }

        .submit-btn {
            flex: 1;
            padding: 15px;
            background: #007952;
            color: white;
            border: none;
            border-radius: 25px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .submit-btn:hover {
            background: #006b47;
            transform: translateY(-2px);
            box-shadow: 0 7px 20px rgba(0, 121, 82, 0.35);
        }

        .cancel-btn {
            padding: 15px 28px;
            background: #6c757d;
            color: white;
            border: none;
            border-radius: 25px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            text-align: center;
        }

        .cancel-btn:hover {
            background: #5a6268;
            transform: translateY(-2px);
        }

        /* ===== LADO DIREITO ===== */
        .edit-info-section {
            flex: 1;
            background: linear-gradient(rgba(0,0,0,0.65), rgba(0,0,0,0.65)),
                url("{{ asset('images/backgraund-loginpng.png') }}");
            background-size: cover;
            background-position: center;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 40px;
            color: white;
        }

        .info-container { max-width: 480px; text-align: center; }

        .info-container h2 { font-size: 2rem; margin-bottom: 16px; }

        .info-container p {
            font-size: 1rem;
            line-height: 1.6;
            margin-bottom: 24px;
            opacity: 0.9;
        }

        .info-box {
            background: rgba(255,255,255,0.1);
            border-radius: 12px;
            padding: 20px 24px;
            text-align: left;
            border-left: 4px solid #007952;
            margin-top: 20px;
        }

        .info-box h3 { font-size: 1rem; margin-bottom: 12px; color: white; }

        .info-box ul { list-style: none; padding: 0; }

        .info-box li {
            margin-bottom: 8px;
            padding-left: 20px;
            position: relative;
            font-size: 0.9rem;
            opacity: 0.9;
        }

        .info-box li:before {
            content: "✓";
            position: absolute;
            left: 0;
            color: #007952;
            font-weight: bold;
        }

        @media (max-width: 900px) {
            .edit-container { flex-direction: column; }
            .edit-form-section { max-width: 100%; overflow-y: auto; }
            .edit-info-section { display: none; }
            body { overflow: auto; height: auto; }
        }

        @media (max-width: 600px) {
            .form-scrollable-wrapper { padding: 24px 16px; }
        }
    </style>
</head>

<body>
    <div class="edit-container">

        <!-- Lado esquerdo - Formulário -->
        <div class="edit-form-section">
            <div class="form-scrollable-wrapper" style="position:relative;">

                <div class="back-btn-container">
                    <a href="{{ route('lrgs-stations.index') }}" class="back-btn">
                        <i class="fas fa-arrow-left"></i>
                        <span>Voltar</span>
                    </a>
                </div>

                <div class="form-container">

                    <div class="form-header">
                        <h1><i class="fas fa-satellite-dish" style="color:#165b9c;font-size:1.6rem;"></i> Nova Estação</h1>
                        <p>Cadastre um novo equipamento LRGS</p>
                    </div>

                    @if ($errors->any())
                        <div class="alert-errors">
                            <i class="fas fa-exclamation-triangle"></i>
                            @foreach ($errors->all() as $error)
                                {{ $error }}<br>
                            @endforeach
                        </div>
                    @endif

                    <form method="POST" action="{{ route('lrgs-stations.store') }}">
                        @csrf

                        <div class="form-section-header">
                            <h3><i class="fas fa-info-circle"></i> Identificação da Estação</h3>
                        </div>

                        <div class="form-group">
                            <label>DCP Address *</label>
                            <div class="input-with-icon">
                                <i class="fas fa-broadcast-tower"></i>
                                <input type="text" name="dcp_address" class="form-control"
                                    value="{{ old('dcp_address') }}"
                                    required maxlength="20"
                                    placeholder="Ex: CE41F1AC">
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Nome da Estação *</label>
                            <div class="input-with-icon">
                                <i class="fas fa-tag"></i>
                                <input type="text" name="station_label" class="form-control"
                                    value="{{ old('station_label') }}"
                                    required maxlength="255"
                                    placeholder="Nome amigável da estação">
                            </div>
                        </div>

                        <div class="btn-container">
                            <button type="submit" class="submit-btn">
                                <i class="fas fa-save"></i> Cadastrar Estação
                            </button>
                            <a href="{{ route('lrgs-stations.index') }}" class="cancel-btn">Cancelar</a>
                        </div>

                    </form>
                </div>
            </div>
        </div>

        <!-- Lado direito - Info -->
        <div class="edit-info-section">
            <div class="info-container">
                <h2>Nova Estação LRGS</h2>
                <p>Informe o endereço DCP e um nome para a estação. Os demais dados serão preenchidos automaticamente pelo sistema.</p>

                <div class="info-box">
                    <h3>Preenchimento Automático</h3>
                    <ul>
                        <li>Latitude e Longitude</li>
                        <!-- <li>Canal de transmissão</li>
                        <li>Intervalo de transmissão</li>
                        <li>Dados técnicos do equipamento</li> -->
                    </ul>
                </div>

                <div class="info-box" style="margin-top:16px; border-left-color:#165b9c;">
                    <h3>Formato do DCP Address</h3>
                    <ul>
                        <li>Código hexadecimal de 8 caracteres</li>
                        <li>Exemplo: CE41F1AC</li>
                    </ul>
                </div>
            </div>
        </div>

    </div>
</body>

</html>
