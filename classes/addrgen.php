<?php
/**
 * Copyright (c) 2012 Matyas Danter
 * Copyright (c) 2012 Chris Savery
 * Copyright (c) 2013 Pavol Rusnak
 *
 * Permission is hereby granted, free of charge, to any person obtaining
 * a copy of this software and associated documentation files (the "Software"),
 * to deal in the Software without restriction, including without limitation
 * the rights to use, copy, modify, merge, publish, distribute, sublicense,
 * and/or sell copies of the Software, and to permit persons to whom the
 * Software is furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included
 * in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS
 * OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL
 * THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES
 * OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE,
 * ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR
 * OTHER DEALINGS IN THE SOFTWARE.
 */
class LCurve {
	public function __construct($prime, $a, $b) {
		$this->a = $a;
		$this->b = $b;
		$this->prime = $prime;
	}
	public function contains($x, $y) {
		return !gmp_cmp(gmp_mod(gmp_sub(gmp_pow($y, 2), gmp_add(gmp_add(gmp_pow($x, 3), gmp_mul($this->a, $x)), $this->b)), $this->prime), 0);
	}
	public static function cmp(LCurve $cp1, LCurve $cp2) {
		return gmp_cmp($cp1->a, $cp2->a) || gmp_cmp($cp1->b, $cp2->b) || gmp_cmp($cp1->prime, $cp2->prime);
	}
}
class LPoint {
	const INFINITY = 'infinity';
	public function __construct(LCurve $curve, $x, $y, $order = null) {
		$this->curve = $curve;
		$this->x = $x;
		$this->y = $y;
		$this->order = $order;
		if (isset($this->curve) && ($this->curve instanceof LCurve)) {
			if (!$this->curve->contains($this->x, $this->y)) {
				throw new ErrorException('Curve does not contain point');
			}
			if ($this->order != null) {
				if (self::cmp(self::mul($order, $this), self::INFINITY) != 0) {
					throw new ErrorException('Self*Order must equal infinity');
				}
			}
		}
	}
	public static function cmp($p1, $p2) {
			if (!($p1 instanceof LPoint)) {
				if (($p2 instanceof LPoint))
					return 1;
				if (!($p2 instanceof LPoint))
					return 0;
			}
			if (!($p2 instanceof LPoint)) {
				if (($p1 instanceof LPoint))
					return 1;
				if (!($p1 instanceof LPoint))
					return 0;
			}
			return gmp_cmp($p1->x, $p2->x) || gmp_cmp($p1->y, $p2->y) || LCurve::cmp($p1->curve, $p2->curve);
	}
	public static function add($p1, $p2) {
		if (self::cmp($p2, self::INFINITY) == 0 && ($p1 instanceof LPoint)) {
			return $p1;
		}
		if (self::cmp($p1, self::INFINITY) == 0 && ($p2 instanceof LPoint)) {
			return $p2;
		}
		if (self::cmp($p1, self::INFINITY) == 0 && self::cmp($p2, self::INFINITY) == 0) {
			return self::INFINITY;
		}
		if (Curve::cmp($p1->curve, $p2->curve) == 0) {
			if (gmp_cmp($p1->x, $p2->x) == 0) {
				if (gmp_mod(gmp_add($p1->y, $p2->y), $p1->curve->prime) == 0) {
					return self::INFINITY;
				} else {
					return self::double($p1);
				}
			}
			$p = $p1->curve->prime;
			$l = gmp_mul(gmp_sub($p2->y, $p1->y), gmp_invert(gmp_sub($p2->x, $p1->x), $p));
			$x3 = gmp_mod(gmp_sub(gmp_sub(gmp_pow($l, 2), $p1->x), $p2->x), $p);
			$y3 = gmp_mod(gmp_sub(gmp_mul($l, gmp_sub($p1->x, $x3)), $p1->y), $p);
			return new Point($p1->curve, $x3, $y3);
		} else {
			throw new ErrorException('Elliptic curves do not match');
		}
	}
	public static function mul($x2, LPoint $p1) {
		$e = $x2;
		if (self::cmp($p1, self::INFINITY) == 0) {
			return self::INFINITY;
		}
		if ($p1->order != null) {
			$e = gmp_mod($e, $p1->order);
		}
		if (gmp_cmp($e, 0) == 0) {
			return self::INFINITY;
		}
		if (gmp_cmp($e, 0) > 0) {
			$e3 = gmp_mul(3, $e);
			$negative_self = new LPoint($p1->curve, $p1->x, gmp_neg($p1->y), $p1->order);
			$i = gmp_div(self::leftmost_bit($e3), 2);
			$result = $p1;
			while (gmp_cmp($i, 1) > 0) {
				$result = self::double($result);
				if (gmp_cmp(gmp_and($e3, $i), 0) != 0 && gmp_cmp(gmp_and($e, $i), 0) == 0) {
					$result = self::add($result, $p1);
				}
				if (gmp_cmp(gmp_and($e3, $i), 0) == 0 && gmp_cmp(gmp_and($e, $i), 0) != 0) {
					$result = self::add($result, $negative_self);
				}
				$i = gmp_div($i, 2);
			}
			return $result;
		}
	}
	public static function leftmost_bit($x) {
		if (gmp_cmp($x, 0) > 0) {
			$result = 1;
			while (gmp_cmp($result, $x) <= 0) {
				$result = gmp_mul(2, $result);
			}
			return gmp_div($result, 2);
		}
	}
	public static function double(LPoint $p1) {
		$p = $p1->curve->prime;
		$a = $p1->curve->a;
		$inverse = gmp_invert(gmp_mul(2, $p1->y), $p);
		$three_x2 = gmp_mul(3, gmp_pow($p1->x, 2));
		$l = gmp_mod(gmp_mul(gmp_add($three_x2, $a), $inverse), $p);
		$x3 = gmp_mod(gmp_sub(gmp_pow($l, 2), gmp_mul(2, $p1->x)), $p);
		$y3 = gmp_mod(gmp_sub(gmp_mul($l, gmp_sub($p1->x, $x3)), $p1->y), $p);
		if (gmp_cmp(0, $y3) > 0)
			$y3 = gmp_add($p, $y3);
		return new LPoint($p1->curve, $x3, $y3);
	}
}
function addr_from_mpk($mpk, $index, $change = false)
{
	// create the ecc curve
	$_p  = gmp_init('FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFEFFFFFC2F', 16);
	$_r  = gmp_init('FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFEBAAEDCE6AF48A03BBFD25E8CD0364141', 16);
	$_b  = gmp_init('0000000000000000000000000000000000000000000000000000000000000007', 16);
	$_Gx = gmp_init('79BE667EF9DCBBAC55A06295CE870B07029BFCDB2DCE28D959F2815B16F81798', 16);
	$_Gy = gmp_init('483ADA7726A3C4655DA4FBFC0E1108A8FD17B448A68554199C47D08FFB10D4B8', 16);
	$curve = new LCurve($_p, 0, $_b);
	$gen = new LPoint($curve, $_Gx, $_Gy, $_r);
	// prepare the input values
	$x = gmp_init(substr($mpk, 0, 64), 16);
	$y = gmp_init(substr($mpk, 64, 64), 16);
	$branch = $change ? 1 : 0;
	$z = gmp_init(hash('sha256', hash('sha256', "$index:$branch:" . pack('H*', $mpk), TRUE)), 16);
	// generate the new public key based off master and sequence points
	$pt = Point::add(new LPoint($curve, $x, $y), LPoint::mul($z, $gen));
	$keystr = pack('H*', '04'
	        . str_pad(gmp_strval($pt->x, 16), 64, '0', STR_PAD_LEFT)
	        . str_pad(gmp_strval($pt->y, 16), 64, '0', STR_PAD_LEFT));
	$vh160 =  '00' . hash('ripemd160', hash('sha256', $keystr, TRUE));
	$addr = $vh160 . substr(hash('sha256', hash('sha256', pack('H*', $vh160), TRUE)), 0, 8);
	$num = gmp_strval(gmp_init($addr, 16), 58);
	$num = strtr($num, '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuv', '123456789ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz');
	$pad = ''; $n = 0;
	while ($addr[$n] == '0' && $addr[$n+1] == '0') {
		$pad .= '1';
		$n += 2;
	}
	return $pad . $num;
}
?>