<?php

return [

    // Textos compartidos entre varios paneles/archivos JS
    'common' => [
        'title_required'       => 'El título o concepto no puede estar vacío.',
        'amount_invalid'       => 'Ingresa un monto válido mayor a 0.',
        'fetch_info_error'     => 'Error :status: No se pudo obtener la información.',
        'connection_error'     => 'Error de conexión. Inténtelo de nuevo.',
        'generic_process_error' => 'Ocurrió un error al procesar la solicitud.',
        'none'                 => 'Ninguno',
        'na'                   => 'N/A',
    ],

    // paneles.js (motor genérico de modales)
    'panel' => [
        'delete_confirm_generic' => '¿Estás seguro de eliminar este registro de :entity?',
    ],

    // agent.js (asistente / chat)
    'agent' => [
        'you'                => 'Tú',
        'ai'                 => 'IA',
        'empty_title'        => '¿En qué te ayudo?',
        'empty_subtitle'     => 'Pregúntame sobre tus finanzas, con base en tus datos reales.',
        'suggestion_1'       => '¿En qué debería enfocarme este mes?',
        'suggestion_2'       => '¿Cómo van mis metas de ahorro?',
        'suggestion_3'       => '¿Qué deuda debería priorizar?',
        'no_conversations'   => 'Aún no tienes conversaciones.',
        'load_error'         => 'No se pudo cargar la conversación.',
        'contact_error'      => 'Error al contactar al asistente.',
        'now'                => 'Ahora',
        'delete_title_attr'  => 'Eliminar conversación',
        'delete_default_title' => 'esta conversación',
        'delete_confirm'     => '¿Eliminar ":titulo"? Esta acción no se puede deshacer.',
        'delete_error'       => 'No se pudo eliminar la conversación.',
    ],

    // profile.js (perfil / cuenta)
    'profile' => [
        'update_error'        => 'No se pudo actualizar la información.',
        'reset_error'         => 'No se pudo completar la eliminación.',
        'delete_account_error' => 'No se pudo eliminar la cuenta.',
    ],

    // info_income.js
    'income' => [
        'loading'          => 'Cargando información del ingreso...',
        'no_id_error'      => 'Error: No se proporcionó el ID del ingreso.',
        'update_error'     => 'Error al actualizar el ingreso.',
        'delete_confirm'   => '¿Deseas eliminar el ingreso programado ":titulo"? Ya no se generarán movimientos automáticos asociados.',
        'delete_error'     => 'No se pudo eliminar el ingreso.',
        'active_badge'     => 'Generación Activa',
        'inactive_badge'   => 'Pausado / Inactivo',
        'frecuencia' => [
            'ninguno'   => 'Ninguno',
            'diario'    => 'Diario',
            'semanal'   => 'Semanal',
            'quincenal' => 'Quincenal',
            'mensual'   => 'Mensual',
            'anual'     => 'Anual',
        ],
    ],

    // info_paymentdebt.js
    'paymentDebt' => [
        'loading'         => 'Cargando información del pago...',
        'update_error'    => 'Error al actualizar el pago.',
        'delete_confirm'  => '¿Deseas eliminar el pago ":titulo"? Esto ajustará de vuelta el saldo de la deuda (y de la billetera de origen, si aplicó).',
        'delete_error'    => 'No se pudo eliminar el pago.',
        'minimum_payment' => 'Pago Mínimo',
        'external'        => 'Externa',
        'wallet_default'  => 'Billetera',
        'concept_required' => 'El concepto del pago no puede estar vacío.',
    ],

    // info_paymentgoal.js
    'paymentGoal' => [
        'loading'         => 'Cargando información del abono...',
        'update_error'    => 'Error al actualizar el abono.',
        'delete_confirm'  => '¿Deseas eliminar el abono ":titulo"? Esto ajustará de vuelta el saldo de la meta (y de la billetera de origen, si aplicó).',
        'delete_error'    => 'No se pudo eliminar el abono.',
        'external'        => 'Externa',
        'wallet_default'  => 'Billetera',
        'concept_required' => 'El concepto del abono no puede estar vacío.',
    ],

    // info_transaction.js
    'transaction' => [
        'loading'        => 'Cargando información...',
        'update_error'   => 'Error al actualizar el movimiento.',
        'delete_confirm' => '¿Deseas eliminar el movimiento ":titulo"? Esta acción no se puede deshacer.',
        'delete_error'   => 'No se pudo eliminar el movimiento.',
        'category_card'  => 'Categoria: :categoria',
        'tipo' => [
            'ingreso' => 'Ingreso',
            'gasto'   => 'Gasto',
            'egreso'  => 'Egreso',
        ],
    ],

    // info_debt.js
    'debt' => [
        'loading'                  => 'Cargando información de la deuda...',
        'update_error'             => 'Error al actualizar la deuda.',
        'delete_confirm'           => '¿Deseas eliminar la deuda ":titulo"? Se eliminará también todo su historial de pagos.',
        'delete_error'             => 'No se pudo eliminar la deuda.',
        'already_paid'             => 'Esta deuda ya está pagada por completo.',
        'name_required'            => 'El nombre de la deuda no puede estar vacío.',
        'due_date_required'        => 'Selecciona una fecha de vencimiento.',
        'payment_concept_required' => 'Ingresa un concepto para el pago.',
        'payment_amount_invalid'   => 'Ingresa un monto de pago válido mayor a 0.',
        'payment_exceeds_balance'  => 'El pago no puede ser mayor al saldo restante ($:restante). Ingresa como máximo esa cantidad.',
        'paid_badge'               => '¡Deuda Pagada!',
        'pending_badge'            => 'Pendiente',
        'prioridad' => [
            'media' => 'Media (Normal)',
            'alta'  => 'Alta (Urgente)',
            'baja'  => 'Baja (Flexible)',
        ],
        'remaining'                => 'Resta: $:monto',
        'original'                 => 'Original: $:monto',
        'chart_label'              => 'Deuda restante ($)',
        'chart_tooltip_payment'    => 'Pago: :titulo (-$:monto)',
        'chart_tooltip_remaining'  => 'Resta: $:monto',
        'card_destination'         => 'Prioridad: :prioridad | Vence: :fecha',
    ],

    // info_goal.js
    'goal' => [
        'loading'                       => 'Cargando información de la meta...',
        'update_error'                  => 'Error al actualizar la meta.',
        'delete_confirm'                => '¿Deseas eliminar la meta ":titulo"? Se eliminará también todo su historial de abonos.',
        'delete_error'                  => 'No se pudo eliminar la meta.',
        'already_completed'             => 'Esta meta ya fue completada.',
        'name_required'                 => 'El nombre de la meta no puede estar vacío.',
        'target_amount_invalid'         => 'Ingresa un monto objetivo válido mayor a 0.',
        'due_date_required'             => 'Selecciona una fecha límite.',
        'contribution_concept_required' => 'Ingresa un concepto para el abono.',
        'contribution_amount_invalid'   => 'Ingresa un monto de abono válido mayor a 0.',
        'completed_badge'               => '¡Meta Completada!',
        'in_progress_badge'             => 'En progreso',
        'no_description'                => 'Sin descripción.',
        'chart_label'                   => 'Progreso de la meta ($)',
        'chart_tooltip_contribution'    => 'Abono: :titulo (+$:monto)',
        'chart_tooltip_accumulated'     => 'Acumulado: $:monto',
        'card_origin'                   => 'Meta: $:objetivo | Inicial: $:inicial',
        'card_destination'              => 'Fecha Límite: :fecha | Estado: :estado',
        'estado' => [
            'activa'     => 'Activa',
            'completada' => 'Completada',
        ],
    ],

    // info_investment.js
    'investment' => [
        'loading'               => 'Cargando información de la inversión...',
        'update_error'          => 'Error al actualizar la inversión.',
        'delete_confirm'        => '¿Deseas eliminar la inversión ":titulo"? Esta acción es irreversible.',
        'delete_error'          => 'No se pudo eliminar la inversión.',
        'name_required'         => 'El nombre de la inversión no puede estar vacío.',
        'initial_amount_invalid' => 'Ingresa un monto invertido válido.',
        'current_value_invalid' => 'Ingresa un valor actual válido.',
        'fixed_income'          => 'Renta Fija',
        'variable_income'       => 'Renta Variable',
        'estado' => [
            'activa'     => 'Activa',
            'finalizada' => 'Finalizada',
            'cancelada'  => 'Cancelada',
        ],
        'card_origin'      => 'Invertido: $:monto',
        'card_destination' => 'Ganancia: :ganancia:tasa | Vence: :fecha',
        'rate_suffix'      => ' | Tasa: :tasa%',
        'no_due_date'      => 'Sin fecha',
        'chart_invested'      => 'Invertido',
        'chart_current_value' => 'Valor Actual',
        'chart_amount_label'  => 'Monto ($)',
    ],

    // info_wallet.js
    'wallet' => [
        'loading'                  => 'Cargando información...',
        'fetch_info_error'         => 'Error :status: No se pudo encontrar la información.',
        'update_error'             => 'Error al actualizar los datos.',
        'delete_confirm'           => '¿Deseas eliminar la billetera ":titulo"? Esta acción es irreversible.',
        'delete_error'             => 'No se pudo eliminar la billetera.',
        'name_required'            => 'El nombre de la billetera no puede estar vacío.',
        'amounts_required'        => 'Los montos no pueden estar vacíos.',
        'amount_unchanged_alert'   => 'El monto actual no ha cambiado. Modifica el saldo o desmarca la opción de registrar movimiento.',
        'movement_title_required'  => 'Ingresa un título para el movimiento.',
        'amount_unchanged_indicator' => 'El monto no ha cambiado.',
        'type_expense'             => 'Tipo: Gasto (-$:monto)',
        'type_income'              => 'Tipo: Ingreso (+$:monto)',
        'initial_amount_card'      => 'Monto Inicial: $:monto',
        'chart_balance_label'      => 'Saldo acumulado ($)',
        'chart_tooltip_movement'   => 'Movimiento: :titulo (:monto)',
        'chart_tooltip_origin'     => 'Origen: :origen',
        'chart_tooltip_destination' => 'Destino: :destino',
        'chart_tooltip_balance'    => 'Saldo: $:monto',
        'tipo' => [
            'ahorro'   => 'Ahorro',
            'debito'   => 'Débito',
            'efectivo' => 'Efectivo',
            'credito'  => 'Crédito',
        ],
    ],

];