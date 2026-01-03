<!-- Commentaires de validation -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-comments me-2"></i>Commentaires de validation</h5>
            </div>
            <div class="card-body">
                @php
                    $statusLabels = [
                        'accepted' => 'Accepté',
                        'rejected' => 'Rejeté',
                        'toreviewed' => 'À réviser',
                        'validated' => 'Validé',
                    ];
                    $statusClasses = [
                        'accepted' => 'bg-success',
                        'rejected' => 'bg-danger',
                        'toreviewed' => 'bg-warning text-dark',
                        'validated' => 'bg-primary',
                    ];
                @endphp
                @foreach($loan->validationHistory as $history)
                    @php
                        $timestamp = $history->trackedDate ?? $history->created_at;
                        $description = $history->operDescription ?? '';
                        $comment = null;
                        if (strpos($description, 'Commentaires:') !== false) {
                            $comment = trim(substr($description, strpos($description, 'Commentaires:') + strlen('Commentaires:')));
                        } elseif (strpos($description, 'Validation finale par le gérant:') !== false) {
                            $comment = trim(substr($description, strlen('Validation finale par le gérant:')));
                        }
                        if ($comment === '') {
                            $comment = null;
                        }
                    @endphp
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <strong>{{ $history->user->fullName ?? $history->user->username }}</strong>
                                @if(!empty($history->user->role))
                                    <span class="badge bg-light text-dark text-uppercase ms-2">{{ str_replace('_', ' ', $history->user->role) }}</span>
                                @endif
                            </div>
                            <small class="text-muted">{{ $timestamp ? $timestamp->format('d/m/Y H:i') : '' }}</small>
                        </div>
                        <div class="mt-2">
                            <span class="badge {{ $statusClasses[$history->recordStatus] ?? 'bg-secondary' }}">
                                {{ $statusLabels[$history->recordStatus] ?? ucfirst($history->recordStatus) }}
                            </span>
                        </div>
                        <p class="mt-2 mb-0 {{ $comment ? '' : 'text-muted fst-italic' }}">
                            {{ $comment ?? 'Aucun commentaire renseigné.' }}
                        </p>
                    </div>
                    @if(!$loop->last)
                        <hr>
                    @endif
                @endforeach
            </div>
        </div>
    </div>
</div>

