<?php

// Classe per rappresentare un intervallo numerico [start, end]
class Interval
{
    public int $start;
    public int $end;

    // Costruttore della classe: accetta due estremi dell’intervallo
    public function __construct(int $start, int $end)
    {
        if ($start > $end) {
            // Verifica che il punto iniziale non sia maggiore di quello finale
            throw new InvalidArgumentException("Start deve essere minore o uguale a End.");
        }
        $this->start = $start;
        $this->end = $end;
    }

    // Metodo per calcolare l'unione tra due intervalli
    public function union(Interval $other): Interval
    {
        // Se gli intervalli non si sovrappongono, restituiamo comunque uno che li contiene entrambi
        // (nota: unione completa con due intervalli disgiunti NON viene gestita come doppio intervallo)
        return new Interval(
            min($this->start, $other->start), // il minimo tra gli inizi
            max($this->end, $other->end)      // il massimo tra le fini
        );
    }

    // Metodo per calcolare l’intersezione tra due intervalli
    public function intersection(Interval $other): ?Interval
    {
        // Se gli intervalli non si toccano (uno finisce prima che l’altro inizi)
        if ($this->end < $other->start || $other->end < $this->start) {
            return null;
        }
        // L'intervallo in comune è tra il massimo dei due inizi e il minimo delle due fini
        return new Interval(
            max($this->start, $other->start),
            min($this->end, $other->end)
        );
    }

    // Metodo per calcolare la differenza (ciò che rimane dell’intervallo corrente dopo aver tolto quello passato)
    public function difference(Interval $other): ?Interval
    {
        // Se non si sovrappongono, restituisco l’intervallo originale
        if ($this->end <= $other->start || $other->end <= $this->start) {
            return new Interval($this->start, $this->end);
        }

        // Se è completamente incluso dentro l'altro intervallo, non resta nulla
        if ($this->start >= $other->start && $this->end <= $other->end) {
            return null;
        }

        // Se inizia prima dell’altro e finisce dentro: ritorna la parte sinistra
        if ($this->start < $other->start && $this->end <= $other->end) {
            return new Interval($this->start, $other->start - 1);
        }

        // Se inizia dentro e finisce dopo l’altro: ritorna la parte destra
        if ($this->start >= $other->start && $this->end > $other->end) {
            return new Interval($other->end + 1, $this->end);
        }

        // Caso più complesso (non gestito in modo completo): l’altro è interno al primo
        // Ritorniamo solo la parte sinistra per semplicità
        return new Interval($this->start, $other->start - 1);
    }

    // Metodo per stampare l’intervallo come stringa leggibile, es. [1,7]
    public function __toString(): string
    {
        return "[" . $this->start . "," . $this->end . "]";
    }
}

// 🔍 Funzione di test: stampa i risultati delle operazioni tra due intervalli
function test(string $label, Interval $a, Interval $b): void
{
    echo "\n=== $label ===\n";
    echo "A = $a, B = $b\n";

    // Unione
    echo "Union: " . $a->union($b) . "\n";

    // Intersezione
    $inter = $a->intersection($b);
    echo "Intersection: " . ($inter ? $inter : "null") . "\n";

    // Differenza
    $diff = $a->difference($b);
    echo "Difference: " . ($diff ? $diff : "null") . "\n";
}

// Esecuzione dei test come da specifiche PDF
test("Test 1", new Interval(1, 7), new Interval(4, 12));   // Output atteso: [1,12], [4,7], [1,3]
test("Test 2", new Interval(1, 4), new Interval(7, 12));   // Output atteso: [1,12], null, [1,4]
test("Test 3", new Interval(4, 12), new Interval(1, 7));   // Output atteso: [1,12], [4,7], [8,12]
