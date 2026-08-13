import { useEffect, useRef, useState } from 'react';
import { Html5Qrcode } from 'html5-qrcode';
import { Barcode, Camera, X } from 'lucide-react';
import { Input } from '../ui/Input';
import { Button } from '../ui/Button';
import { Modal } from '../ui/Modal';
import { toast } from 'react-hot-toast';

export function ScannerInput({ onScan, onSearch }: { onScan: (barcode: string) => void; onSearch: () => void }) {
  const [value, setValue] = useState('');
  const [scanning, setScanning] = useState(false);
  const scannerRef = useRef<Html5Qrcode | null>(null);

  const submit = (barcode: string) => {
    const trimmed = barcode.trim();
    if (!trimmed) return;
    onScan(trimmed);
    setValue('');
  };

  const startCamera = async () => {
    setScanning(true);

    const scanner = new Html5Qrcode('qr-reader');
    scannerRef.current = scanner;

    try {
      await scanner.start(
        { facingMode: 'environment' },
        { fps: 10, qrbox: { width: 220, height: 220 } },
        (decodedText) => {
          onScan(decodedText);
          stopCamera();
        },
        () => {
          // ignore non-decodable frames
        },
      );
    } catch (error) {
      console.error(error);
      toast.error('Unable to start camera. Check permissions.');
      setScanning(false);
    }
  };

  const stopCamera = async () => {
    try {
      await scannerRef.current?.stop();
      scannerRef.current?.clear();
    } catch {
      // ignore
    }
    scannerRef.current = null;
    setScanning(false);
  };

  useEffect(() => {
    return () => {
      stopCamera();
    };
  }, []);

  return (
    <div className="flex gap-2">
      <div className="relative flex-1">
        <Barcode className="pointer-events-none absolute left-3 top-2.5 size-5 text-gray-400" />
        <Input
          value={value}
          onChange={(e) => setValue(e.target.value)}
          onKeyDown={(e) => {
            if (e.key === 'Enter') submit(value);
          }}
          placeholder="Scan barcode or type to search..."
          className="pl-10"
          autoFocus
        />
      </div>
      <Button type="button" variant="secondary" onClick={startCamera} title="Scan with camera">
        <Camera className="size-4" />
      </Button>
      <Button type="button" variant="secondary" onClick={onSearch} title="Advanced search (F2)">
        Search
      </Button>

      <Modal open={scanning} onClose={stopCamera} title="Scan Barcode">
        <div className="flex flex-col items-center">
          <div id="qr-reader" className="w-full overflow-hidden rounded-lg" />
          <p className="mt-3 text-sm text-gray-500">Point the camera at the product barcode.</p>
        </div>
      </Modal>
    </div>
  );
}