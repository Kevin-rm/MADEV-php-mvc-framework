<style>
    .error-container {
        background: #c51244;
        padding: 20px;
        color: #ffffff;
        margin: 20px auto;
        position: relative;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    }

    .error-container h1 {
        font-size:  2em;
        font-weight: bold;
    }

    .error-container p {
        font-size:  1em;
        margin-bottom: 1.5em;
    }

    .error-container::before {
        content: "";
        position: absolute;
        left:  50%;
        bottom: -10px;
        border-width:  10px;
        border-style: solid;
        border-color: transparent transparent #c51244 transparent;
        transform: translateX(-50%);
    }

    .error-container pre {
        color: black;
        white-space: pre-wrap;
        background: #eee;
        padding:  10px;
    }
</style>

<div class="col-10 mt-4 error-container">
    <h1 class="mb-4">Une erreur s'est produite</h1>
    <p><strong>Type :             </strong><?= get_class($exception)    ?></p>
    <p><strong>Message d'erreur : </strong><?= $exception->getMessage() ?></p>
    <p><strong>Code d'erreur :    </strong><?= $exception->getCode()    ?></p>
    <p><strong>Fichier :          </strong><?= $exception->getFile()    ?></p>
    <p><strong>Ligne :            </strong><?= $exception->getLine()    ?></p>
    <p><strong>Trace :            </strong></p>
    <pre><?= $exception->getTraceAsString() ?></pre>
</div>