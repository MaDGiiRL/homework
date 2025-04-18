<?php

declare(strict_types=1);

/**
 * Classe che rappresenta un intervallo chiuso [start, end] su tipi comparabili:
 * - int
 * - float
 * - \DateTimeImmutable
 *
 * Gestisce le operazioni di:
//   • union        (unione/riunione di due intervalli)
//   • intersection (intersezione)
//   • difference   (differenza insiemistica, può restituire fino a 2 sotto‑intervalli)
 *
 * Problemi e considerazioni già commentati nei metodi:
 * - validazione tipi (numeric vs DateTime)
 * - ordine corretto start ≤ end
 * - discreto vs continuo (boundary adjustment)
 * - restituzione di array per union/difference e null per intersection vuota
 */
final class Interval
{
    public function __construct(
        public readonly int|float|\DateTimeImmutable $start,
        public readonly int|float|\DateTimeImmutable $end,
    ) {
        // 1) Tipo coerente: entrambi numeric o entrambi DateTimeImmutable
        $isNumericStart = is_int($start) || is_float($start);
        $isNumericEnd   = is_int($end)   || is_float($end);
        if ($isNumericStart !== $isNumericEnd) {
            throw new \InvalidArgumentException(
                'Start e End devono essere entrambi numerici o entrambi DateTimeImmutable'
            );
        }

        // 2) Ordine corretto
        if ($start > $end) {
            throw new \InvalidArgumentException('Start deve essere ≤ End');
        }
    }

    /**
     * Unione di due intervalli.
     *
     * @param Interval $other
     * @return Interval[]  Array di 1 o 2 intervalli (2 se disgiunti)
     */
    public function union(self $other): array
    {
        // Se disgiunti e non contigui (no sovrapposizione né contiguità estrema):
        if ($this->end < $other->start) {
            return [$this, $other];
        }
        if ($other->end < $this->start) {
            return [$other, $this];
        }

        // Altrimenti almeno toccano: unisco in un singolo intervallo
        $newStart = $this->start < $other->start ? $this->start : $other->start;
        $newEnd   = $this->end   > $other->end   ? $this->end   : $other->end;

        return [new self($newStart, $newEnd)];
    }

    /**
     * Intersezione di due intervalli.
     *
     * @param Interval $other
     * @return Interval|null  Nuovo intervallo o null se non si sovrappongono
     */
    public function intersection(self $other): ?self
    {
        $newStart = $this->start > $other->start ? $this->start : $other->start;
        $newEnd   = $this->end   < $other->end   ? $this->end   : $other->end;

        // Se l'intervallo risultante è valido (start ≤ end), lo restituisco; altrimenti null
        return $newStart <= $newEnd
            ? new self($newStart, $newEnd)
            : null;
    }

    /**
     * Differenza (A \ B) ossia porzione di questo intervallo non coperta da $other.
     *
     * @param Interval $other
     * @return Interval[]  Fino a 2 intervalli risultanti (0, 1 o 2)
     *
     * ATTENZIONE sui confini:
     * - Con tipi int discreti, per ottenere [1,3] da [1,7]\[4,12] serve sottrarre 1 da 4.
     * - Con float/DateTimeImmutable non ha senso "-1": serve un concetto di epsilon o DateInterval.
     * Qui restituiamo intervalli chiusi “raw” e lasciamo al chiamante l’eventuale post‑processing.
     */
    public function difference(self $other): array
    {
        $result = [];

        // Caso 1: completamente disgiunti → todo quest'intervallo
        if ($other->end < $this->start || $other->start > $this->end) {
            return [$this];
        }

        // Caso 2: pezzo a sinistra di $other (start .. other.start)
        if ($other->start > $this->start) {
            $result[] = new self($this->start, $other->start);
        }

        // Caso 3: pezzo a destra di $other (other.end .. end)
        if ($other->end < $this->end) {
            $result[] = new self($other->end, $this->end);
        }

        return $result;
    }

    /**
     * Rappresentazione stringa per debug/logging.
     */
    public function __toString(): string
    {
        // Per DateTimeImmutable, usiamo il formato ISO8601
        $format = fn($v) => $v instanceof \DateTimeImmutable
            ? $v->format(\DateTimeInterface::ATOM)
            : (string)$v;

        return sprintf('[%s, %s]', $format($this->start), $format($this->end));
    }
}
