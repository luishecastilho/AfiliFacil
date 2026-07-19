import { useCallback, useState } from 'react';

export function useConfirm() {
    const [state, setState] = useState(null);

    const confirm = useCallback(
        (options = {}) =>
            new Promise((resolve) => {
                setState({
                    ...options,
                    onConfirm: () => {
                        setState(null);
                        resolve(true);
                    },
                    onCancel: () => {
                        setState(null);
                        resolve(false);
                    },
                });
            }),
        [],
    );

    return { confirmState: state, confirm };
}
