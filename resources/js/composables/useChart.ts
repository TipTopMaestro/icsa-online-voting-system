import { Chart, registerables } from 'chart.js';

// Register all Chart.js components at once (simpler approach)
Chart.register(...registerables);

export function createChart(canvas: HTMLCanvasElement, config: any) {
  return new Chart(canvas, config);
}
